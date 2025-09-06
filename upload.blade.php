<!DOCTYPE html>
<html>
<head>
    <title>Upload Files</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2>Upload Files</h2>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                        @if(session('files'))
                            <ul>
                                @foreach(session('files') as $url)
                                    <li><a href="{{ $url }}" target="_blank">{{ $url }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="/upload" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Choose Files</label>
                        <input type="file" name="files[]" class="form-control" multiple>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload Files</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
