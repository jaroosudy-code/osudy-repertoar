<?php

namespace App\Http\Controllers;

use App\Models\Setlist;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function csv(Setlist $setlist): StreamedResponse
    {
        $filename = Str::slug($setlist->name) . '_' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($setlist) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            if ($setlist->event_type === 'entertainment') {
                $totalAllSeconds = 0;
                $totalBreakSeconds = 0;

                foreach ($setlist->rounds()->with('setlistSongs.song')->get() as $round) {
                    fputcsv($handle, ['=== ' . strtoupper($round->name) . ' ==='], ';');
                    fputcsv($handle, ['#', 'Názov piesne', 'Čas', 'Tempo', 'Typ'], ';');

                    $roundSeconds = 0;
                    foreach ($round->setlistSongs as $idx => $entry) {
                        $song = $entry->song;
                        fputcsv($handle, [
                            $idx + 1,
                            $song->name,
                            $song->duration_formatted,
                            $song->tempo === 'fast' ? 'Rýchla' : 'Pomalá',
                            $song->type === 'own' ? 'Vlastná' : 'Cover',
                        ], ';');
                        $roundSeconds += $song->duration_seconds;
                    }

                    $totalAllSeconds += $roundSeconds;
                    fputcsv($handle, ['', 'Čas kola', $this->fmtTime($roundSeconds), '', ''], ';');

                    if ($round->break_after_minutes > 0) {
                        fputcsv($handle, ['', 'Prestávka', $round->break_after_minutes . ' min', '', ''], ';');
                        $totalBreakSeconds += $round->break_after_minutes * 60;
                    }
                    fputcsv($handle, [], ';');
                }

                fputcsv($handle, ['CELKOVÝ ČAS HUDBY', $this->fmtTime($totalAllSeconds)], ';');
                fputcsv($handle, ['CELKOVÝ ČAS VRÁTANE PRESTÁVOK', $this->fmtTime($totalAllSeconds + $totalBreakSeconds)], ';');
            } else {
                fputcsv($handle, ['#', 'Názov piesne', 'Čas', 'Tempo', 'Typ'], ';');
                $totalSeconds = 0;

                foreach ($setlist->concertSongs()->with('song')->get() as $idx => $entry) {
                    $song = $entry->song;
                    fputcsv($handle, [
                        $idx + 1,
                        $song->name,
                        $song->duration_formatted,
                        $song->tempo === 'fast' ? 'Rýchla' : 'Pomalá',
                        $song->type === 'own' ? 'Vlastná' : 'Cover',
                    ], ';');
                    $totalSeconds += $song->duration_seconds;
                }

                fputcsv($handle, ['', 'CELKOVÝ ČAS', $this->fmtTime($totalSeconds)], ';');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function fmtTime(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;
        return sprintf('%d:%02d:%02d', $h, $m, $s);
    }
}
