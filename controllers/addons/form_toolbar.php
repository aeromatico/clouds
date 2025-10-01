<div data-control="toolbar">
    <a href="<?= Backend::url('aero/clouds/addons') ?>" class="btn btn-default oc-icon-chevron-left">
        <?= e(trans('backend::lang.form.return_to_list')) ?>
    </a>
    <?= $this->formRenderPreview() ?>
    <button
        type="submit"
        data-request="onSave"
        data-hotkey="ctrl+s, cmd+s"
        data-load-indicator="<?= e(trans('backend::lang.form.saving')) ?>"
        class="btn btn-primary">
        <?= e(trans('backend::lang.form.save')) ?>
    </button>
    <button
        type="button"
        data-request="onSave"
        data-request-data="close:1"
        data-hotkey="ctrl+enter, cmd+enter"
        data-load-indicator="<?= e(trans('backend::lang.form.saving')) ?>"
        class="btn btn-default">
        <?= e(trans('backend::lang.form.save_and_close')) ?>
    </button>
    <button
        type="button"
        class="btn btn-danger oc-icon-trash-o"
        data-request="onDelete"
        data-load-indicator="<?= e(trans('backend::lang.form.deleting')) ?>"
        data-request-confirm="<?= e(trans('backend::lang.form.confirm_delete')) ?>">
        <?= e(trans('backend::lang.form.delete')) ?>
    </button>
</div>
