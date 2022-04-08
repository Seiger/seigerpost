<div class="table-responsive">
    <table class="table table-condensed table-hover sectionTrans">
        <thead>
        <tr>
            <th style="width:70px;text-align:center;">ID</th>
            <th style="text-align:center;">{{$_lang["name"]}}</th>
            <th style="width:70px;text-align:center;">{{$_lang["spost_views"]}}</th>
            <th style="width:150px;text-align:center;">{{$_lang["page_data_published"]}}</th>
            <th style="width:110px;text-align:center;">{{$_lang['spost_visible']}}</th>
            <th style="width:60px;text-align:center;">{{$_lang["type"]}}</th>
            <th style="width:260px;text-align:center;">{{$_lang["onlineusers_action"]}}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($sPost->posts() as $post)
            <tr>
                <td><b>{{$post['post']}}</b></td>
                <td>
                    <img src="{{$sPost->evo->getConfig('site_url', '/')}}{{$sPost->imgResize($post['cover'], 60, 40)}}" alt="{{$sPost->evo->getConfig('site_url', '/')}}{{$post['cover']}}" class="post-thumbnail">
                    <a href="{{$sPost->evo->getConfig('site_url', '/')}}{{$post['alias']}}/" target="_blank"><b>{{$post['pagetitle']}}</b></a>
                </td>
                <td><b>{{$post['views']}}</b></td>
                <td><b>{{$post['pub_date']}}</b></td>
                <td>
                    @if($post['published'])
                        <span class="badge badge-success">{{$_lang["page_data_published"]}}</span>
                    @else
                        <span class="badge badge-secondary">{{$_lang["page_data_unpublished"]}}</span>
                    @endif
                </td>
                <td>
                    @if($post['type'] == 0)<span class="badge badge-info">{{$_lang['spost_'.$post['type']]}}</span>@endif
                    @if($post['type'] == 1)<span class="badge badge-dark">{{$_lang['spost_'.$post['type']]}}</span>@endif
                </td>
                <td style="text-align:center;">
                    <a href="{{$url}}&get=post&i={{$post['post']}}" class="btn btn-outline-success"><i class="fa fa-pencil"></i>&emsp;{{$_lang['edit']}}</a>
                    <a href="#" data-href="{{$url}}&get=postDelete&i={{$post['post']}}" data-toggle="modal" data-target="#confirmDelete" data-id="{{$post['post']}}" data-name="{{$post['pagetitle']}}" class="btn btn-outline-danger"><i class="fa fa-trash"></i>&emsp;{{$_lang['remove']}}</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@push('scripts.bot')
    <div id="actions">
        <div class="btn-group">
            <a href="{!!$url!!}&get=postAdd" class="btn btn-success" title="{{$_lang["spost_add_help"]}}">
                <i class="fa fa-plus-circle"></i>&emsp;<span>{{$_lang["spost_add"]}}</span>
            </a>
        </div>
    </div>
@endpush