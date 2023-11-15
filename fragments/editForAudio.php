<section class="rex-page-section">
    <div class="panel panel-edit">
        <header class="panel-heading">
            <div class="panel-title">2. Text vor Verarbeitung überarbeiten</div>
        </header>
        <div class="panel-body">

            <form method="post">
                <div class="form-group">

                    <div class="form-group">
                        <label>Sprache:</label>
                        <select class="form-control" name="targetLang">
                            <option value="de">Deutsch</option>
                            <option value="en">Englisch</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Text der in Audio umgewandelt werden soll:</label>
                        <textarea rows="20" name="text" class="form-control"><?= $this->text ?></textarea>
                    </div>

                    <input type="hidden" name="func" value="genAudio">
                    <input type="hidden" name="artId" value="<?= $this->artId ?>">
                </div>
                <button type="submit" class="btn btn-primary">Audio generieren</button>
            </form>

        </div>
    </div>
</section>