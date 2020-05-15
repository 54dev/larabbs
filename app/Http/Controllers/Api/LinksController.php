<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\LinkResource;
use App\Models\Link;

class LinksController extends Controller
{
    public function index(Link $link)
    {
        $links = $link->getAllCached();

        return LinkResource::collection($links);
    }
}
