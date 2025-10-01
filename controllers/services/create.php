<?php Block::put('breadcrumb') ?>
    <ul>
        <li><a href="<?= Backend::url('aero/clouds/services') ?>">Services</a></li>
        <li><?= e($this->pageTitle) ?></li>
    </ul>
<?php Block::endPut() ?>

<?= Form::open(['class' => 'layout']) ?>

    <div class="layout-row">
        <?= $this->formRender() ?>
    </div>

    <div class="form-buttons">
        <div class="loading-indicator-container">
            <button
                type="submit"
                data-request="onSave"
                data-request-data="redirect:0"
                data-hotkey="ctrl+s, cmd+s"
                data-load-indicator="<?= e(trans('backend::lang.form.creating')) ?>"
                class="btn btn-primary">
                <?= e(trans('backend::lang.form.create')) ?>
            </button>
            <button
                type="button"
                data-request="onSave"
                data-request-data="close:1"
                data-hotkey="ctrl+enter, cmd+enter"
                data-load-indicator="<?= e(trans('backend::lang.form.creating')) ?>"
                class="btn btn-default">
                <?= e(trans('backend::lang.form.create_and_close')) ?>
            </button>
            <span class="btn-text">
                <?= e(trans('backend::lang.form.or')) ?> <a href="<?= Backend::url('aero/clouds/services') ?>"><?= e(trans('backend::lang.form.cancel')) ?></a>
            </span>
        </div>
    </div>

<?= Form::close() ?>