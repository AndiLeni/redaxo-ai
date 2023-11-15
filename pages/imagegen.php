<?php

use OpenAI\Testing\ClientFake;
use OpenAI\Responses\Images\CreateResponse;

if (rex_request_method() == 'post') {

    $func = $prompt = rex_post("func", "string", "");

    if ($func == "saveImg") {
        // save image to media pool
        $url = rex_post("url", "string", "");
        $prompt = rex_post("prompt", "string", "");

        $fileName = rex_formatter::intlDateTime(time(), 'yyyy-MM-dd_hh-mm') . ".jpg";

        if (rex_media::get($fileName)  === null) {
            $path = rex_path::media($fileName);

            try {
                $socket = rex_socket::factoryUrl($url);
                $response = $socket->followRedirects(5)->doGet();
                if ($response->isOk()) {
                    $response->writeBodyTo($path);

                    $data = [];
                    $data['title'] = '';
                    $data['category_id'] = rex_config::get("ai", "category");
                    $data['file'] = [
                        'name' => $prompt . ".jpg",
                        'path' => $path,
                    ];

                    try {
                        $result = rex_media_service::addMedia($data, false);
                        echo rex_view::info($result["message"]);
                    } catch (rex_api_exception $e) {
                        // throw new rex_functional_exception($e->getMessage());
                        echo rex_view::error($e->getMessage());
                    }
                } else {
                    echo $response->getStatusCode();
                    echo $response->getStatusMessage();
                }
            } catch (rex_socket_exception $e) {
                echo $e->getMessage();
            }
        }
    }

    if ($func == "generateImg") {
        // generate images
        $client = OpenAI::client(rex_config::get("ai", "openaiKey"));

        $prompt = rex_post("prompt", "string", "");
        $n = rex_post("n", "int", 1);


        // debug fake client
        // $client = new ClientFake([
        //     CreateResponse::fake([
        //         'data' => [
        //             [
        //                 'url' => 'https://picsum.photos/257/257',
        //             ],
        //             [
        //                 'url' => 'https://picsum.photos/256/256',
        //             ],
        //             [
        //                 'url' => 'https://picsum.photos/255/255',
        //             ],
        //             [
        //                 'url' => 'https://picsum.photos/254/254',
        //             ],
        //         ],
        //     ]),
        // ]);


        $response = $client->images()->create([
            'prompt' => $prompt,
            'n' => $n,
            'size' => '256x256',
            'response_format' => 'url',
        ]);

        $response = $response->toArray();
        // ['created' => 1589478378, data => ['url' => 'https://oaidalleapiprodscus...', ...]]
        // dump($response);

        echo '<div class="row">';
        foreach ($response["data"] as $img) {
            $fragment = new rex_fragment();
            $fragment->setVar('url', $img["url"], false);
            $fragment->setVar('prompt', $prompt, false);
            echo $fragment->parse('imageGen.php');
        }
        echo '</div>';
    }
}




?>




<section class="rex-page-section">
    <div class="panel panel-edit">
        <header class="panel-heading">
            <div class="panel-title">Bilder Generieren</div>
        </header>
        <div class="panel-body">
            <form method="post">
                <div class="form-group">
                    <label>Was soll das Bild darstellen?</label>
                    <textarea name="prompt" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label>Wie viele Bilder sollen generiert werden?</label>
                    <input type="number" name="n" class="form-control" value="1">
                </div>
                <input type="hidden" name="func" value="generateImg">
                <button type="submit" class="btn btn-primary">Neue Bilder generieren</button>
            </form>

        </div>
    </div>
</section>