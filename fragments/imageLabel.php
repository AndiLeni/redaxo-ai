<div class="col-12 col-sm-6 col-md-3">
    <div class="panel panel-default">
        <div class="panel-body">
            <form method="post">
                <div class="form-group">
                    <img class="img-responsive" src="<?= $this->url ?>">
                    <p><?= $this->title ?></p>
                    <input type="hidden" name="url" value="<?= $this->url ?>">
                    <input type="hidden" name="imgId" value="<?= $this->imgId ?>">
                    <input type="hidden" name="func" value="labelmg">
                </div>
                <button type="submit" class="btn btn-primary">Labeln</button>
            </form>
        </div>
    </div>
</div>