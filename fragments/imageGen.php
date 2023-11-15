<div class="col-12 col-sm-6 col-md-3">
    <div class="panel panel-default">
        <div class="panel-body">
            <form method="post">
                <div class="form-group">
                    <img class="img-responsive" src="<?= $this->url ?>">
                    <input type="hidden" name="url" value="<?= $this->url ?>">
                    <input type="hidden" name="prompt" value="<?= $this->prompt ?>">
                    <input type="hidden" name="func" value="saveImg">
                </div>
                <button type="submit" class="btn btn-primary">Speichern</button>
            </form>
        </div>
    </div>
</div>