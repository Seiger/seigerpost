<?php
// actions buttons templates
$action = isset($_REQUEST['a']) ? $_REQUEST['a'] : '';
if ($modx->getConfig('global_tabs') && !isset($_SESSION['stay'])) {
    $_REQUEST['stay'] = 2;
}
if (isset($_REQUEST['stay'])) {
    $_SESSION['stay'] = $_REQUEST['stay'];
} elseif (isset($_SESSION['stay'])) {
    $_REQUEST['stay'] = $_SESSION['stay'];
}
$stay = isset($_REQUEST['stay']) ? $_REQUEST['stay'] : '';
$save = (isset($get) && in_array($get, ['post', 'postAdd'])) ? true : false;
$attTag = (isset($get) && in_array($get, ['post', 'postAdd', 'tags'])) ? true : false;
?>
<div id="actions">
    <div class="btn-group">
        @if($save)
            <a id="Button1" class="btn btn-success" href="javascript:void(0);" onclick="saveForm('#post');">
                <i class="fa fa-floppy-o"></i>
                <span>{{$_lang['save']}}</span>
            </a>
        @endif
        @if($attTag)
            <a id="Button2" class="btn btn-info" href="#" data-toggle="modal" data-target="#addTag">
                <i class="fa fa-hashtag"></i>
                <span>{{$_lang['spost_add_tag']}}</span>
            </a>
        @endif
        @if(!empty($duplicate))
            <a id="Button6" class="btn btn-secondary" href="javascript:;" onclick="actions.duplicate();">
                <i class="{{ $_style['icon_clone'] }}"></i>
                <span>{{ ManagerTheme::getLexicon('duplicate') }}</span>
            </a>
        @endif
        @if(!empty($delete))
            <a id="Button3" class="btn btn-secondary" href="javascript:;" onclick="actions.delete();">
                <i class="{{ $_style['icon_trash'] }}"></i>
                <span>{{ ManagerTheme::getLexicon('delete') }}</span>
            </a>
        @endif
        @if(!empty($cancel))
            <a id="Button5" class="btn btn-secondary" href="javascript:;" onclick="actions.cancel();">
                <i class="{{ $_style['icon_cancel'] }}"></i>
                <span>{{ ManagerTheme::getLexicon('cancel') }}</span>
            </a>
        @endif
        @if(!empty($preview))
            <a id="Button4" class="btn btn-secondary" href="javascript:;" onclick="actions.view();">
                <i class="{{ $_style['icon_eye'] }}"></i>
                <span>{{ ManagerTheme::getLexicon('preview') }}</span>
            </a>
        @endif
    </div>
</div>
