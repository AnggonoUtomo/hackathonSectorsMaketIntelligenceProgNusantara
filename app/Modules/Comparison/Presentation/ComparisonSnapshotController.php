<?php

namespace App\Modules\Comparison\Presentation;

use App\Modules\Comparison\Application\GetComparisonSnapshot;
use App\Modules\Comparison\Application\ListComparisonSnapshots;
use App\Modules\Comparison\Application\SaveComparisonSnapshot;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ComparisonSnapshotController
{
    public function index(Request $request, ListComparisonSnapshots $snapshots)
    {
        return Inertia::render('nusalens/comparison-snapshots', [
            'snapshots' => $snapshots->execute((string) $request->user()->id),
        ]);
    }

    public function store(Request $request, SaveComparisonSnapshot $save)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'symbols' => ['required', 'array', 'min:1', 'max:3'],
            'symbols.*' => ['required', 'string', 'regex:/\A[A-Za-z0-9]{4}(?:\.JK)?\z/i'],
            'payload' => ['required', 'array'],
            'payload.companies' => ['nullable', 'array', 'max:3'],
            'payload.sections' => ['nullable', 'array'],
            'base_snapshot_id' => ['nullable', 'string', 'ulid'],
        ]);

        $snapshot = $save->execute(
            (string) $request->user()->id,
            $validated['title'] ?? null,
            $validated['symbols'],
            $validated['payload'],
            $validated['base_snapshot_id'] ?? null,
        );

        return redirect()->route('compare.snapshots.show', ['snapshot' => $snapshot['id']])
            ->with('status', 'Snapshot perbandingan tersimpan.');
    }

    public function show(Request $request, string $snapshot, GetComparisonSnapshot $snapshots)
    {
        $data = $snapshots->execute((string) $request->user()->id, $snapshot);

        abort_if($data === null, 404);

        return Inertia::render('nusalens/comparison-snapshot-detail', [
            'snapshot' => $data,
        ]);
    }
}
