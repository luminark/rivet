<?php

namespace Luminark\Rivet\Test;

use Luminark\Rivet\Traits\ControlsRivetsTrait;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    use ControlsRivetsTrait;

    public function create(Request $request)
    {
        $image = $this->createRivet(Image::class, $request->all());

        return $image;
    }

    public function update($id, Request $request)
    {
        $image = Image::findOrFail($id);

        $this->updateRivet($image, $request->all());

        return $image;
    }
}
