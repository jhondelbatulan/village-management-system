<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsSearchController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;

    public function __construct(protected AnalyticsSearchService $service) {}

    public function search(Request $request): View
    {
        $query = $request->input('q');
        $villageId = $this->getVillageId(auth()->user());
        $results = $this->service->search($query, $villageId);
        $filterOptions = $this->service->getFilterOptions();

        return view('analytics.search.index', compact('query', 'results', 'filterOptions'));
    }

    public function advancedFilter(Request $request): View
    {
        $villageId = $this->getVillageId(auth()->user());

        $filters = $request->only([
            'village',
            'program',
            'membership',
            'employment_status',
            'education',
            'income_range',
            'skills',
            'healthcare',
            'resident_type',
            'date_from',
            'date_to',
            'q',
        ]);

        $builder = $this->service->advancedFilter($filters, $villageId);
        $residents = $builder->orderBy('last_name')->orderBy('first_name')->get();
        $results = [
            'residents' => $residents,
            'total' => $residents->count(),
            'query' => $request->input('q', ''),
        ];
        $filterOptions = $this->service->getFilterOptions();

        return view('analytics.search.index', compact('filters', 'results', 'filterOptions'));
    }

}
