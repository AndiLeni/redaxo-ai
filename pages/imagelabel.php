<?php

// GCP docs
// https://cloud.google.com/vision/docs/labels?hl=en


$func = rex_post("func", "string", "");

if ($func == "labelmg") {
    $imgId = rex_post("imgId", "string", "");
    // dump($imgId);

    $sql = rex_sql::factory();
    $imageFilename = $sql->setQuery("select id, filename, title from " . rex::getTable("media") . " where id = ?", [$imgId]);
    $imageFilename = $imageFilename->getValue("filename");

    $imageData = rex_file::get(rex_path::media($imageFilename));
    $base64Image = base64_encode($imageData);
    // dump($base64Image);


    $body = [
        "requests" => [
            [
                "image" => [
                    "content" => $base64Image,
                    // "source" =>
                    // [
                    //     "imageUri" => rex::getServer() . "media/" . $imageFilename
                    // ]

                ],
                "features" =>
                [
                    "maxResults" => "15",
                    "type" => "LABEL_DETECTION"
                ]

            ]
        ]
    ];

    // dump($body);
    // dump(json_encode($body));


    try {

        $socket = rex_socket::factoryUrl("https://vision.googleapis.com/v1/images:annotate");
        $socket->addHeader("X-goog-api-key", rex_config::get("ai", "gcpKey"));
        $socket->addHeader("Accept", "*/*");
        $socket->addHeader("Content-Type", "application/json; charset=utf-8");

        $response = $socket->doPost(json_encode($body));

        if ($response->isOk()) {

            $body = json_decode($response->getBody(), true);
            $labels = array_column($body["responses"][0]["labelAnnotations"], "description");
            // dump($labels);

            // translation
            $socketTranslate = rex_socket::factoryUrl("https://translation.googleapis.com/language/translate/v2");
            $socketTranslate->addHeader("X-goog-api-key", rex_config::get("ai", "gcpKey"));
            $socketTranslate->addHeader("Accept", "*/*");
            $socketTranslate->addHeader("Content-Type", "application/json; charset=utf-8");

            $toTranslate = [
                "q" => $labels,
                "source" => "en",
                "target" => "de",
                // "format" => "text"
            ];

            $responseTranslate = $socketTranslate->doPost(json_encode($toTranslate));

            if ($responseTranslate->isOk()) {
                $bodyTranslate = $responseTranslate->getBody();
                $labelsDe = json_decode($bodyTranslate, true);
                $labelsDe = array_column($labelsDe["data"]["translations"], "translatedText");
                $labelsDe = implode(", ", $labelsDe);

                echo rex_view::success($labelsDe);

                $sql = rex_sql::factory();
                $sql->setQuery("update " . rex::getTable("media") . " set title = ? where id = ?", [$labelsDe, $imgId]);
                // dump($imageFilename);
            }
        } else {
            $body = $response->getBody();
            echo rex_view::error($response->getStatusCode() . " - " . $response->getStatusMessage());
            echo rex_view::error($body);
        }
    } catch (rex_socket_exception $e) {
        echo $e->getMessage();
    }
}


$sql = rex_sql::factory();
$imagesNoTitle = $sql->getArray("select id, filename, title from " . rex::getTable("media") .  " where filetype like 'image/%'");
// $imagesNoTitle = $sql->getArray("select id, filename, title from " . rex::getTable("media") . " where title = ''");
// dump($imagesNoTitle);

foreach ($imagesNoTitle as $img) {
    // dump($img);
    $imgSrc = rex_media_manager::getUrl("rex_media_medium", $img["filename"]);
    // dump($imgSrc);
    $fragment = new rex_fragment();
    $fragment->setVar('url', $imgSrc, false);
    $fragment->setVar('imgId', $img["id"], false);
    $fragment->setVar('title', $img["title"], false);
    echo $fragment->parse('imageLabel.php');
}
