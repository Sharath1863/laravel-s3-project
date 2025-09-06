<!DOCTYPE html>
<html>
<head>
    <title>AWS S3 File Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
         <div class="upload-container">
        <h2>Welcome to AWS S3 File Manager - Testing Automatic Deployment</h2>
        
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
                @if(session('url'))
                    <div style="margin-top: 10px;">
                        <strong>Uploaded file:</strong>
                        <a href="{{ session('url') }}" target="_blank">View File</a>
                    </div>
                @endif
            </div>
        @endif        margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background: #f7f7f7;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .upload-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        .upload-area {
            border: 2px dashed #ccc;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            background: #fff;
        }
        .upload-area:hover {
            border-color: #2196F3;
            background: #f8f9fa;
        }
        .upload-area.dragover {
            border-color: #2196F3;
            background: #e3f2fd;
        }
        .file-input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }
        .upload-text {
            color: #666;
            margin: 10px 0;
            font-size: 16px;
        }
        .selected-file {
            margin-top: 15px;
            padding: 10px;
            background: #e3f2fd;
            border-radius: 4px;
            display: none;
            font-size: 14px;
        }
        .upload-btn {
            background: #2196F3;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 15px;
        }
        .upload-btn:hover {
            background: #1976D2;
        }
        .upload-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            display: none;
        }
        }
        .custom-file-upload input[type="file"] {
            display: none;
        }
        .file-info {
            margin: 10px 0;
            color: #666;
        }
        .submit-btn {
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .submit-btn:hover {
            background: #45a049;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .alert-error {
            background: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <h2>Upload File</h2>
        
        @if(session('success'))
            <div class="alert alert-success">
                <div>{{ session('success') }}</div>
                <div style="margin-top: 10px;">
                    <strong>Uploaded file:</strong>
                    <a href="{{ session('url') }}" target="_blank">View File</a>
                </div>
            </div>
        @endif

    @if(session('error'))
        <div style="color: red;">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="/upload" method="POST" enctype="multipart/form-data" id="uploadForm">
        @csrf
        <label class="custom-file-upload">
            <input type="file" id="fileInput" name="file" required accept="image/*,application/pdf,.doc,.docx">
            <div id="dropText">
                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <p>Drop your file here or click to browse</p>
            </div>
            <div id="fileInfo" class="file-info" style="display: none;">
                Selected file: <span id="fileName">No file chosen</span>
            </div>
        </label>
        <button type="submit" class="submit-btn" id="submitBtn" disabled>
            Upload File
        </button>
    </form>

    <script>
        const fileInput = document.getElementById('fileInput');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const dropText = document.getElementById('dropText');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('uploadForm');

        // Handle file selection
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                fileName.textContent = file.name;
                fileInfo.style.display = 'block';
                dropText.style.display = 'none';
                submitBtn.disabled = false;
            } else {
                resetForm();
            }
        });

        // Handle drag and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            document.querySelector('.custom-file-upload').addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        document.querySelector('.custom-file-upload').addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const file = dt.files[0];
            
            fileInput.files = dt.files;
            if (file) {
                fileName.textContent = file.name;
                fileInfo.style.display = 'block';
                dropText.style.display = 'none';
                submitBtn.disabled = false;
            }
        }

        function resetForm() {
            fileName.textContent = 'No file chosen';
            fileInfo.style.display = 'none';
            dropText.style.display = 'block';
            submitBtn.disabled = true;
        }

        // Add loading state to form
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Uploading...';
        });
    </script>
</body>
</html>
</body>
</html>
