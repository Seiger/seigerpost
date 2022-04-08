<div class="tab-page" id="navigationTab">
    <h2 class="tab"><i class="fa fa-header" aria-hidden="true"></i>{{$_lang['navigation_tab']}}</h2>
    <script>tpResources.addTabPage(document.getElementById('navigationTab'));</script>
    <input type="hidden" name="page_nav_form" value="{{$post['post']}}">
    <input type="hidden" name="page_nav_tvId[{{$tvId}}]" value="{{$tvId}}"
    @if(isset($heading) && is_array($heading))
        @foreach($heading as $key => $head)
            <br/>
            <div class="row form-row">
                <div class="col-auto col-title">
                    <label for="provider" class="warning" data-key="provider"><strong>{{$head}}</strong></label>
                </div>
                id = <div class="col">
                    <input type="text" id="navigation_title_{{$lang_default}}" name="page_nav[{{$tvId}}][{{$lang_default}}][navigation_id][]"
                           value="{{$selected[$lang_default]['navigation_id'][$key] ?? ''}}" class="form-control" maxlength="255">
                </div>
            </div>
            @foreach($sPost->langTabs() as $lang => $tabName)
                <div class="row form-row">
                    <div class="col-auto col-title">
                        <label for="provider" class="warning" data-key="provider">{{strtoupper($lang)}}</label>
                    </div>
                    <div class="col">
                        <input type="text" id="navigation_title_{{$lang}}" name="page_nav[{{$tvId}}][{{$lang}}][navigation_title][]"
                               value="{{$selected[$lang]['navigation_title'][$key] ?? ''}}" class="form-control" maxlength="255">
                    </div>
                </div>
            @endforeach
            <br/>
        @endforeach
    @endif
    @if(isset($heading_epilog) && is_array($heading_epilog))
        @foreach($heading_epilog as $key => $head)
            <br/>
            <div class="row form-row">
                <div class="col-auto col-title">
                    <label for="provider" class="warning" data-key="provider"><strong>{{$head}}</strong></label>
                </div>
                id = <div class="col">
                    <input type="text" id="navigation_title_{{$lang_default}}" name="page_nav[{{$tvId_epilog}}][{{$lang_default}}][navigation_id][]"
                           value="{{$selectEpilog[$lang_default]['navigation_id'][$key] ?? ''}}" class="form-control" maxlength="255">
                </div>
            </div>
            @foreach($sPost->langTabs() as $lang => $tabName)
                <div class="row form-row">
                    <div class="col-auto col-title">
                        <label for="provider" class="warning" data-key="provider">{{strtoupper($lang)}}</label>
                    </div>
                    <div class="col">
                        <input type="text" id="navigation_title_{{$lang}}" name="page_nav[{{$tvId_epilog}}][{{$lang}}][navigation_title][]"
                               value="{{$selectEpilog[$lang]['navigation_title'][$key] ?? ''}}" class="form-control" maxlength="255">
                    </div>
                </div>
            @endforeach
            <br/>
        @endforeach
    @endif
</div>