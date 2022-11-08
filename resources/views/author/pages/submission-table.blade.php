@extends('author.layouts.default')


@section('title', 'submission Table')

@section('bodys')
<div class="card-body">
    <table id="bootstrap-data-table" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Conferences Title</th>
                <th>Title </th>
                <th>Abstract</th>
                <th>Keyword</th>
                <th>Track Name</th>
                <th>File</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $d)
            <tr>
                <td>
                    {{ $d->conferences_title }}
                </td>
                <td>
                    {{ $d->title }}
                </td>
                <td>
                    {{ Str::limit($d->abstract, 50)  }}
                </td>
                <td>
                    {{ Str::limit($d->tags, 50)  }}
                </td>
                <td>
                    {{ $d->tracks_name }}
                </td>
                <td>
                    {{ Str::limit($d->file_name, 12) }}
                </td>
                <td>
                    <a href="{{ url('author/submission/delete/'.$d->id) }}" class="btn btn-info">Delete</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@stop