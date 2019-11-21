<template>
  <div>
    <div class="form_controls">
      <div class="upload_button_holder">
        <input type="file" id="fileElem" style="display: none;" onchange="handleFiles(this.files)">
        <a href="#" id="fileSelect">{{ trans('Upload a file') }}</a>
      </div>
    </div>
    <div class="progress-bar" id="progress-bar">
      <div class="progress" id="progress"></div>
    </div>
  </div>
</template>

<script>
  import I18n from '../mixins/I18n'
  import cloudinary from 'cloudinary-core'

  export default {
    mixins: [
      I18n
    ],

    props: {
      url: {
        type: String,
        default: ''
      }
    },

    /*
    const cloudName = 'elukas';
const unsignedUploadPreset = 'testUpload';

var fileSelect = document.getElementById("fileSelect"),
  fileElem = document.getElementById("fileElem"),
    urlSelect = document.getElementById("urlSelect");

fileSelect.addEventListener("click", function(e) {
  if (fileElem) {
    fileElem.click();
  }
  e.preventDefault(); // prevent navigation to "#"
}, false);

urlSelect.addEventListener("click", function(e) {
  uploadFile('https://res.cloudinary.com/demo/image/upload/sample.jpg')
  e.preventDefault(); // prevent navigation to "#"
}, false);


// ************************ Drag and drop ***************** //
function dragenter(e) {
  e.stopPropagation();
  e.preventDefault();
}

function dragover(e) {
  e.stopPropagation();
  e.preventDefault();
}

dropbox = document.getElementById("dropbox");
dropbox.addEventListener("dragenter", dragenter, false);
dropbox.addEventListener("dragover", dragover, false);
dropbox.addEventListener("drop", drop, false);

function drop(e) {
  e.stopPropagation();
  e.preventDefault();

  var dt = e.dataTransfer;
  var files = dt.files;

  handleFiles(files);
}

// *********** Upload file to Cloudinary ******************** //
function uploadFile(file) {
  var url = `https://api.cloudinary.com/v1_1/${cloudName}/upload`;
  var xhr = new XMLHttpRequest();
  var fd = new FormData();
  xhr.open('POST', url, true);
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

  // Reset the upload progress bar
   document.getElementById('progress').style.width = 0;

  // Update progress (can be used to show progress indicator)
  xhr.upload.addEventListener("progress", function(e) {
    var progress = Math.round((e.loaded * 100.0) / e.total);
    document.getElementById('progress').style.width = progress + "%";

    console.log(`fileuploadprogress data.loaded: ${e.loaded},
  data.total: ${e.total}`);
  });

  xhr.onreadystatechange = function(e) {
    if (xhr.readyState == 4 && xhr.status == 200) {
      // File uploaded successfully
      var response = JSON.parse(xhr.responseText);
      // https://res.cloudinary.com/cloudName/image/upload/v1483481128/public_id.jpg
      var url = response.secure_url;
      // Create a thumbnail of the uploaded image, with 150px width
      var tokens = url.split('/');
      tokens.splice(-2, 0, 'w_150,c_scale');
      var img = new Image(); // HTML5 Constructor
      img.src = tokens.join('/');
      img.alt = response.public_id;
      document.getElementById('gallery').appendChild(img);
    }
  };

  fd.append('upload_preset', unsignedUploadPreset);
  fd.append('tags', 'browser_upload'); // Optional - add tag for image admin in Cloudinary
  fd.append('file', file);
  xhr.send(fd);
}

// *********** Handle selected files ******************** //
var handleFiles = function(files) {
  for (var i = 0; i < files.length; i++) {
    uploadFile(files[i]); // call the function to upload the file
  }
};
    */

    mounted() {
      // https://codepen.io/team/Cloudinary/pen/QgpyOK
      console.log('Cloudinary mounted')
    }
  }
</script>

<style scoped>
  .progress-bar {
    height: 8px;
    margin-top: 4px;
    position: relative;
    width: 200px;
  }

  .progress-bar .progress {
    background-color: #ff0000;
    height: 8px;
    width: 0;
  }
</style>
