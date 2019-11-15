<?php

namespace BBS\Nova\Translation\Http\Controllers;

class LabelsController
{
    /**
     * Setup labels matrix endpoint.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function matrix()
    {
        return response()->json([], 200);
    }
}
