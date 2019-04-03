<?php

/**
 * Author: Zbigniew 'zibi' Jarosik <zibi@nora.pl>
 */

use JupyterPHP\zPHP;

class Vega
{
    /** @var \Litipk\JupyterPHP\zPHP */
    private $zPHP;

    public function __construct(zPHP $zPHP)
    {
        $this->zPHP = $zPHP;
    }
    public function __invoke()
    {
        $this->zPHP->send(
            'display_data',
            [
                'data'=>[
                    "application/vnd.vegalite.v2+json"=>json_decode('{
                        "$schema": "https://vega.github.io/schema/vega-lite/v2.json",
                        "description": "A simple bar chart with embedded data.",
                        "data": {
                            "values": [
                                {"a": "A", "b": 28}, {"a": "B", "b": 55}, {"a": "C", "b": 43},
                                {"a": "D", "b": 91}, {"a": "E", "b": 81}, {"a": "F", "b": 53},
                                {"a": "G", "b": 91}, {"a": "H", "b": 87}, {"a": "I", "b": 52}
                            ]
                        },
                        "mark": "bar",
                        "encoding": {
                            "x": {"field": "a", "type": "ordinal"},
                            "y": {"field": "b", "type": "quantitative"}
                        }
                    }'),
                    "text/plain"=> "<VegaLite 2 object> If you see this message, it means the renderer has not been properly enabled for the frontend that you are using. For more information, see https://altair-viz.github.io/user_guide/troubleshooting.html "
                ],
                'metadata'=>[]
            ]
        );
    }
}
