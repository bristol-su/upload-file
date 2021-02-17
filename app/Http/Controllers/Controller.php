<?php

namespace BristolSU\Module\UploadFile\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class Controller
{
    use AuthorizesRequests {
        authorize as baseAuthorize;
    }

    use DispatchesJobs, ValidatesRequests;

    public function authorize($ability, $arguments = [])
    {
        return $this->baseAuthorize(
            alias() . '.' . $ability,
            $arguments
        );
    }

    public function paginate(Collection $items)
    {
        $perPage = request()->input('per_page', 10);
        $page = request()->input('page', 1);

        $slicedItems = $items->forPage($page, $perPage)->values();

        return $this->paginationResponse($slicedItems, $items->count());
    }

    public function paginationResponse($items, $count)
    {
        $perPage = request()->input('per_page', 10);
        $page = request()->input('page', 1);

        return (new LengthAwarePaginator(
            $items,
            $count,
            $perPage,
            $page,
            ['path' => url(request()->path())]
        ))->appends('per_page', $perPage);
    }

}
