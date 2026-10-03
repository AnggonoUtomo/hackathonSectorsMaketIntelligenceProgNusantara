<?php

namespace App\Modules\Comparison\Presentation;

use App\Modules\Comparison\Application\GetComparisonSnapshot;
use App\Modules\Comparison\Application\ListComparisonSnapshots;
use App\Modules\Comparison\Application\ManageComparisonSnapshot;
use App\Modules\Comparison\Application\SaveComparisonSnapshot;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ComparisonSnapshotController
{
    public function index(Request $request, ListComparisonSnapshots $snapshots)
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'page' => ['nullable', 'integer', 'min:1', 'max:10000']]);
        $result = $snapshots->execute((string) $request->user()->id, $filters['q'] ?? '', (int) ($filters['page'] ?? 1));

        return Inertia::render('nusalens/comparison-snapshots', [
            'snapshots' => $result['items'],
            'pagination' => ['total' => $result['total'], 'page' => $result['page'], 'lastPage' => $result['lastPage']],
            'filters' => ['q' => $filters['q'] ?? ''],
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
            'payload.companies.*' => ['array'],
            'payload.companies.*.symbol' => ['required', 'string', 'regex:/\A[A-Z0-9]{4}\z/'],
            'payload.companies.*.receipt' => ['required', 'string', 'max:200000'],
            'payload.sections' => ['nullable', 'array'],
            'payload.sections.*' => ['array', 'max:3'],
            'payload.sections.*.*' => ['array'],
            'payload.sections.*.*.receipt' => ['required', 'string', 'max:1000000'],
            'payload.scores' => ['nullable', 'array', 'max:3'],
            'payload.scores.*' => ['string', 'ulid'],
            'base_snapshot_id' => ['nullable', 'string', 'ulid'],
        ]);

        try {
            $snapshot = $save->execute(
                (string) $request->user()->id,
                $validated['title'] ?? null,
                $validated['symbols'],
                $validated['payload'],
                $validated['base_snapshot_id'] ?? null,
            );
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['payload' => $exception->getMessage()]);
        }

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

    public function update(Request $request, string $snapshot, ManageComparisonSnapshot $manage)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:120']]);
        abort_unless($manage->rename((string) $request->user()->id, $snapshot, $data['title']), 404);

        return back();
    }

    public function destroy(Request $request, string $snapshot, ManageComparisonSnapshot $manage)
    {
        abort_unless($manage->delete((string) $request->user()->id, $snapshot), 404);

        return redirect()->route('compare.snapshots.index');
    }
}
