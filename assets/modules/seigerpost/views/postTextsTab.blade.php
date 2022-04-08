@if($lang != 'base')<h4><b>{{$_lang['slang_lang_'.$lang]}} {{$_lang['spost_lang']}}</b></h4>@endif
<div class="row form-row">
    <div class="row-col col-lg-12 col-12">
        <div class="row form-row">
            <div class="col-auto col-title">
                <label for="{{$lang}}_pagetitle" class="warning" data-key="pagetitle">{{$_lang["resource_title"]}}</label>
                <i class="fa fa-question-circle" data-tooltip="{{$_lang["resource_title_help"]}}"></i>
            </div>
            <div class="col">
                <input type="text" id="{{$lang}}_pagetitle" class="form-control" name="{{$lang}}[pagetitle]" maxlength="255" value="{{$texts[$lang]['pagetitle'] ?? ''}}" onchange="documentDirty=true;" spellcheck="true">
            </div>
        </div>

        <div class="row form-row">
            <div class="col-auto col-title">
                <label for="{{$lang}}[seo_title]" class="warning" data-key="{{$lang}}[seo_title]">{{$_lang['seo_title']}}</label>
                <i class="fa fa-question-circle"
                   data-tooltip="{{$_lang['seo_title_help']}}"></i>
            </div>
            <div class="col" data-lang="{{$lang}}">
                <div class="input-group">
                    <input type="text" id="provider_seotitle_{{$lang}}" class="form-control " name="{{$lang}}[seo_title]"
                           maxlength="255" value="{{$texts[$lang]['seo_title'] ?? ''}}" onchange="documentDirty=true;"
                           spellcheck="true">
                </div>
            </div>
        </div>

        <div class="row form-row">
            <div class="col-auto col-title"><label for="seo_description" class="warning" data-key="seo_description">{{$_lang['seo_description']}}</label>
                <i class="fa fa-question-circle" data-tooltip="{{$_lang['seo_description_help']}}"></i>
            </div>
            <div class="col" data-lang="{{$lang}}">
                <div class="input-group">
                    <textarea id="provider_seodescription_{{$lang}}" class="form-control" name="{{$lang}}[seo_description]" rows="3" wrap="soft"
                              onchange="documentDirty=true;">{{$texts[$lang]['seo_description'] ?? ''}}</textarea>
                </div>
            </div>
        </div>

        <div class="row form-row">
            <div class="col-auto col-title">
                <label for="{{$lang}}_introtext" class="warning" data-key="introtext">{{$_lang["resource_summary"]}}</label>
                <i class="fa fa-question-circle" data-tooltip="{{$_lang["resource_summary_help"]}}"></i>
            </div>
            <div class="col">
                <textarea id="{{$lang}}_introtext" class="form-control" name="{{$lang}}[introtext]" rows="5" wrap="soft" onchange="documentDirty=true;">{{$texts[$lang]['introtext'] ?? ''}}</textarea>
            </div>
        </div>

        <div class="row form-row form-row-richtext">
            <div class="col-auto col-title">
                <label for="{{$lang}}_content" class="warning" data-key="content">{{$_lang["resource_content"]}}</label>
            </div>
            <div class="col">
                <textarea id="{{$lang}}_content" class="form-control" name="{{$lang}}[content]" cols="40" rows="15" onchange="documentDirty=true;">{{$texts[$lang]['content'] ?? ''}}</textarea>
            </div>
        </div>

        <div class="row form-row form-row-richtext">
            <div class="col-auto col-title">
                <label for="{{$lang}}_epilog" class="warning" data-key="epilog">{{$_lang["spost_epilog"]}}</label>
                <i class="fa fa-question-circle" data-tooltip="{{$_lang["spost_epilog_help"]}}"></i>
            </div>
            <div class="col">
                <textarea id="{{$lang}}_epilog" class="form-control" name="{{$lang}}[epilog]" cols="40" rows="15" onchange="documentDirty=true;">{{$texts[$lang]['epilog'] ?? ''}}</textarea>
            </div>
        </div>
    </div>
</div>