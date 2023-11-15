<?php

$func = rex_post("func", "string", "");

if ($func == "loadArticle") {
    $artId = rex_post("artId", "string", "");

    $fileName = $artId . '.mp3';

    if (rex_media::get($fileName)  !== null) {
        echo rex_view::error("Dieser Artikel ist bereits vertont. Vor neuer Erstellung muss die alte Audio Datei manuell im Medienpool gelöscht werden");
    }

    $artContent = new rex_article_content($artId);
    $text = $artContent->getArticle();
    $text = strip_tags($text);

    $fragment = new rex_fragment();
    $fragment->setVar('text', $text, false);
    $fragment->setVar('artId', $artId, false);
    echo $fragment->parse('editForAudio.php');
}

if ($func == "genAudio") {
    $artId = rex_post("artId", "string", "");
    $text = rex_post("text", "string", "");
    $targetLang = rex_post("targetLang", "string", "");


    if ($targetLang == "de") {
        $languageCode = "de-DE";
        $name = "de-DE-Neural2-B";
        $ssmlGender = "MALE";
    }
    if ($targetLang == "en") {
        $languageCode = "en-US";
        $name = "en-US-Neural2-I";
        $ssmlGender = "MALE";
    }


    $body = [
        "input" => [
            "text" => $text
        ],
        "voice" => [
            "languageCode" => $languageCode,
            "name" => $name,
            "ssmlGender" => $ssmlGender,
        ],
        "audioConfig" => [
            "audioEncoding" => "MP3"
        ]
    ];

    $fileName = $artId . '.mp3';

    if (rex_media::get($fileName)  === null) {
        try {

            $socket = rex_socket::factoryUrl("https://texttospeech.googleapis.com/v1/text:synthesize");
            $socket->addHeader("X-goog-api-key", rex_config::get("ai", "gcpKey"));
            $socket->addHeader("Accept", "*/*");
            $socket->addHeader("Content-Type", "application/json; charset=utf-8");

            $response = $socket->doPost(json_encode($body));

            // dump($socket);
            // dump($response);

            if ($response->isOk()) {
                $path = rex_path::media($fileName);

                $body = json_decode($response->getBody(), true);
                $decodedAudio = base64_decode($body["audioContent"]);
                rex_file::put(rex_path::media($fileName), $decodedAudio);

                $data = [];
                $data['title'] = '';
                $data['category_id'] = rex_config::get("ai", "category");
                $data['file'] = [
                    'name' => $fileName,
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
    } else {
        echo rex_view::error("Dieser Artikel ist bereits vertont. Vor neuer Erstellung muss die alte Audio Datei manuell im Medienpool gelöscht werden");
    }
}



$sql = rex_sql::factory();
$articles = $sql->getArray("select id, name from " . rex::getTable("article"));


?>

<section class="rex-page-section">
    <div class="panel panel-edit">
        <header class="panel-heading">
            <div class="panel-title">1. Artikel wählen</div>
        </header>
        <div class="panel-body">
            <form method="post">
                <div class="form-group">
                    <label>Welcher Artikel soll geladen werden?</label>
                    <select name="artId" class="form-control">
                        <?php foreach ($articles as $article) : ?>
                            <option value="<?= $article['id'] ?>">
                                <?= $article['name'] ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <input type="hidden" name="func" value="loadArticle">
                <button type="submit" class="btn btn-primary">Artikeltext laden</button>
            </form>

        </div>
    </div>
</section>