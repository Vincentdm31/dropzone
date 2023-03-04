<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel</title>

    <style lang="">
        .drop-area {
            border: 0.2rem dashed #6583fe;
            padding: 2rem;
            border-radius: 0.25rem;
            background-color: #fff;
            text-align: center;
            font-size: 1.5rem;
            transition: 0.25s background-color ease-in-out;
        }

        .drop-area.highlight {
            border-color: red
        }

        .drop-area .drop-img {
            max-width: 10vw;
            cursor: pointer;
        }

        .drop-area form input {
            display: none;
        }

        .gallery {
            display: inline-grid;
            margin-top: 2vh;
            justify-items: center;
            align-items: center;
            grid-template-columns: repeat(6, 1fr);
            grid-template-rows: 1fr;
            grid-column-gap: 20px;
            grid-row-gap: 20px;
        }

        .gallery .img-preview {
            position: relative;
        }

        .gallery .cross {
            max-width: 1.5vw;
            height: auto;
            position: absolute;
            top: 0;
            right: 0;
            cursor: pointer;
            transform: translate(50%, -50%)
        }

        .gallery img {
            max-width: 10vw;
            height: auto;
        }

        .progress {
            position: absolute;
            top: 50%;
            left: 0;
            width: 80%;
            transform: translate(10%, -50%)
        }

        progress.success {
            accent-color: rgb(34, 255, 152);
        }

        progress.error {
            accent-color: rgb(255, 50, 50);
        }

        @media screen and (max-width: 960px) {
            .drop-area .drop-img {
                max-width: 15vw;
            }

            .gallery {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                grid-template-rows: 1fr;
                grid-column-gap: 20px;
                grid-row-gap: 20px;
            }

            .gallery img {
                max-width: 20vw;
                height: auto;
            }

            .gallery .cross {
                max-width: 2vw;
            }
        }

        @media screen and (max-width: 600px) {
            .drop-area .drop-img {
                max-width: 15vw;
            }

            .gallery {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: 1fr;
                grid-column-gap: 10px;
                grid-row-gap: 10px;
            }

            .gallery img {
                max-width: 30vw;
                height: auto;
            }

            .gallery .cross {
                max-width: 5vw;
            }
        }
    </style>
</head>

<body class="">
    <div id="drop-area-1" class="drop-area">
        <form class="form">
            <input type="file" id="fileElem" onchange="">
            <label class="button" for="fileElem">
                <img class="drop-img" src="{{ asset('upload.png') }}">
            </label>
        </form>
        <div class="gallery" />
    </div>
    </div>
    <script>
        const file_preview_css_config = {
            'progress_bar_succes_color': 'green',
            'progress_bar_error_color': 'red',
            'cross_remove': "{{ asset('cross_red.png') }}"
        }

        class FilePreview{
            constructor(_dropX, _file, _css_config) {
                this.dropX = _dropX
                this.file = _file;
                this.css_config = _css_config;
                this.progressBar = undefined;

                this.previewFile(this.file)

                this.preview_type = {
                    'image': '',
                    'pdf': ''
                }
            }

            previewFile(file) {
                console.log(file);
                let reader = new FileReader()
                reader.readAsDataURL(file)
                reader.onloadend = () => {
                    let file_preview = `
                        <div id="prev-${file.name.replace('.', '_ext_')}" class="img-preview">
                            <img data-filename="${file.name.replace('.', '_ext_')}" src="${reader.result}"/>
                            <img id="del-${file.name.replace('.', '_ext_')}" class="cross" src="${this.css_config['cross_remove']}"/>
                            <progress id="progress-${file.name.replace('.', '_ext_')}" class="progress" max=100 value=0></progress>
                        </div>
                        `
                    this.dropX.gallery.insertAdjacentHTML('beforeend', file_preview);
                    
                    this.progressBar = document.querySelector('#progress-' + file.name.replace('.', '_ext_'));

                    document.querySelector(`#del-${file.name.replace('.', '_ext_')}`).addEventListener('click', (e) => {
                        console.log(e)
                        this.removeFile(e.target.id.replace('_ext_', '.'))
                    })

                    this.initializeProgress();

                }
            }

            initializeProgress() {
                this.progressBar.value = 0;
            }

            updateProgress(total) {
                this.progressBar.value = total
            }

            setProgressStatus(success){
                this.progressBar.classList.add(success ? 'success' : 'error')
            }

            removeFile(file){
                const url = this.dropX.delRoute.replace(':id', file).replace('del-', '');

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                })
                .then(res => {
                    if(res.status === 200){
                        console.log('file', file)
                        const file_preview = document.querySelector('#prev-' + file)
                    }else if(res.status === 400){
                        console.log('errrrrr')
                    }
                })
                .catch(err => console.error('err', err))
            }
        }

        class DropX{
            constructor(_config){
                this.dropArea = _config['dropArea'];
                this.uploadRoute = _config['uploadRoute']
                this.delRoute = _config['delRoute']
                this.progressBar = this.dropArea.querySelector('.progress')
                this.gallery = this.dropArea.querySelector('.gallery')
                this.files = [];
                this.files_preview = [];

                this.init();
            }

            init() {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    this.dropArea.addEventListener(eventName, this.preventDefaults)   
                    // document.body.addEventListener(eventName, preventDefaults)
                });

                ['dragenter', 'dragover'].forEach(eventName => {
                  this.dropArea.addEventListener(eventName, () => this.highlight())
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    this.dropArea.addEventListener(eventName, () => this.unhighlight())
                })

                this.dropArea.addEventListener('drop', (e) => this.handleDrop(e))
            }

            preventDefaults (e) {
                e.preventDefault()
            }

            highlight(e) {
                this.dropArea.classList.add('highlight')
            }
             
            unhighlight(e) {
                this.dropArea.classList.remove('highlight')
            }

            handleDrop(e) {
                var dt = e.dataTransfer
                var files = dt.files
                this.handleFiles(files)
            }

            handleFiles(files) {
                files = [...files]
                files.forEach(file => {
                    const fp = new FilePreview(this, file, file_preview_css_config);
                    this.files_preview.push(fp);
                    setTimeout(() => {
                        this.uploadFile(file, fp)
                    }, 150);
                })
            }

            uploadFile(file, fp) {
                const formData = new FormData()
                formData.append('file', file);
                
                const xhr = new XMLHttpRequest();

                xhr.open('POST', this.uploadRoute, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                xhr.upload.addEventListener("progress", (e) => {
                  fp.updateProgress( (e.loaded * 100.0 / e.total) || 100)
                })

                xhr.addEventListener('readystatechange', (e) => {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                      fp.setProgressStatus(1)
                      fp.updateProgress(100)
                    }
                    else if (xhr.readyState == 4 && xhr.status != 200) {
                      const errors = JSON.parse(xhr.response).err;
                      fp.setProgressStatus(false)

                      for (const [key, value] of Object.entries(errors)) {
                        console.log(`${key}: ${value}`);
                      }
                    }
                })

                formData.append('file', file)
                xhr.send(formData)
            }
        }

        const config = {
            dropArea: document.querySelector("#drop-area-1"),
            uploadRoute: "{{ route('file.store') }}",
            delRoute: "{{ route('file.delete', ':id') }}",
        }

        const dropX = new DropX(config);
    </script>
</body>

</html>