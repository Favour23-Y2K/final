<?php
if (isset($post)) {
    $thepost = 'post_' . ($post_id * $_SESSION['safe']);
} else {
    $thepost = NULL;
}

if (isset($_COOKIE[$target])) {
    // Decode the cookie value to retrieve the original content including emojis
    $savedVal = urldecode($_COOKIE[$target]);
} else {
    $savedVal = NULL;
}
?>

<form id="messageForm<?= $thepost.$target ?>" tabindex="4" action="action.php" enctype="multipart/form-data" method="POST" class="chat-box fixed-bottom">
    <input type="hidden" name="action" value="send_message">
    <input type="hidden" name="message" value="true">
    <input type="text" name="reply" value="<?= $thepost; ?>" id="reply<?= $thepost.$target ?>" class="hidden-input" />
    <input type="text" name="target" value="<?= $target; ?>" id="target<?= $thepost.$target ?>" class="hidden-input"/>

    <div class="emojiBox"></div>

    <div id="previeewArea<?= $thepost.$target ?>" class="col-12 image-preview" style="position:absolute; margin-top:-9.9em; height:6em; pointer-events:none;"></div>

    <?php

        $fire = FALSE;

        if(isset($post)){
            if($post['user_id'] == $USER) {
                echo '<pre>Cannot Reply Your own Post</pre>';
            } else {
                if($post['comments'] == 1){
                    $fire = TRUE;
                } else {
                    echo '<pre>Comments are Disabled on this Post</pre>';
                }
            }
        } elseif(isset($chats)){
            if($chats['chat_type'] == '1'){
                if(!array_key_exists($other_user['user_id'], $_SESSION['friends'])){
                    echo '<pre>You seem to be NOT CONNECTED with this User. Connect with this User to Chat</pre>';
                } elseif(array_key_exists($other_user['user_id'], $_SESSION['friends'])){
                    $fire = TRUE;
                }
            }elseif($chats['chat_type'] == '2'){
                if($chats['locked'] == 1){
                    foreach ($ischat as $esc => $vee) {
                        if($USER == $vee['user_id']){
                            if ($vee['status'] == '2') {
                                // echo $chatBox;
                                $fire = TRUE;
                            }else{
                                echo '<pre>This Group is Locked and only Admins can Send Messages</pre>';
                            }
                        }
                    }
                }else{
                    // echo $chatBox;
                    $fire = TRUE;
                }
            }
        }

    ?>

<?php if($fire == TRUE): ?>

    <input type="file" id="media<?= $thepost.$target ?>" name="media[]" capture multiple accept="image/*,video/*,application/pdf" data-max-size="41943040" />
    <label for="media<?= $thepost.$target ?>">
        <i class="fas fa-photo-video"></i>
    </label>
    <div class="vnTimer" id="vnTimer<?= $thepost.$target ?>" style="display: none;">00:00</div> <!-- Timer display -->
    <div class="pausedText" id="pausedText<?= $thepost.$target ?>" style="display: none;">PAUSED</div> <!-- Timer display -->
    <button type="button" class="btn btn-primary" id="deleteButton<?= $thepost.$target ?>" style="display: none;"><i class="fas fa-trash"></i></button>
    <textarea class="chat-textarea col-9"
    style="
        max-height: 4.6em; /* Set your desired maximum height */
        overflow-y: auto;  /* Allows scrolling when max height is reached */
        "
    name="caption" id="caption<?= $thepost.$target ?>" placeholder="Send a Message" class="form-control"><?php echo $savedVal; ?></textarea>
    <div class="col-8" id="voicenoteArea<?= $thepost.$target ?>">
        <div id="audioPreviewContainer<?= $thepost.$target ?>">
            <audio id="audio-<?= $thepost.$target ?>" style="display: none;"></audio>
            <div class="audio-controls" id="audio-controls<?= $thepost.$target ?>" style="display: none;">
                <div class="col-9 audio-holder" id="audio-holder<?= $thepost.$target ?>">
                    <div class="play-btn col-3" id="play-btn<?= $thepost.$target ?>" onclick="togglePlay('<?= $thepost.$target ?>')"><i class="fas fa-play"></i></div>
                    <div class="timeline col-9" id="timeline<?= $thepost.$target ?>">
                        <div class="progress-timeline col-12" id="progress-timeline<?= $thepost.$target ?>">
                            <div id="progress-inner<?= $thepost.$target ?>" class="progress-inner" style="width: 0%; background-color: black;"></div>
                        </div>
                        <div class="mini-info col-12" id="mini-info<?= $thepost.$target ?>">
                            <div class="vn-length col-7" id="vn-length<?= $thepost.$target ?>">
                                <small id="vn-length-value<?= $thepost.$target ?>">0:00</small> <!-- Display the length of the voice note -->
                            </div>
                            <div class="vn-size col-5" id="vn-size<?= $thepost.$target ?>">
                                <small id="vn-size-value<?= $thepost.$target ?>">0 KB</small> <!-- Display the size of the voice note -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-3 audio-warp" id="audio-warp<?= $thepost.$target ?>">
                    <div class="vn-speed row btn btn-outline-dark" id="vn-speed<?= $thepost.$target ?>" onclick="toggleSpeed('<?= $thepost.$target ?>')">x1</div>
                    <div class="countdown-timer" id="countdown-timer<?= $thepost.$target ?>">00:00</div>
                </div>
            </div>
        </div>
        <div id="oscilloscopeContainer<?= $thepost.$target ?>" style="display: none;">
            <canvas id="oscilloscopeCanvas<?= $thepost.$target ?>"></canvas>
        </div>
        <div id="progressContainer<?= $thepost.$target ?>" style="display: none;">
            <div class="progress">
                <div class="progress-bar" id="progressBar<?= $thepost.$target ?>" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
    <div class="formButtons">
        <button name="message" type="submit" id="sendMessage<?= $thepost.$target ?>" style="line-height:.5em;" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
        <button id="voiceNoteBtn<?= $thepost.$target ?>" type="button" class="btn btn-primary"><i class="fas fa-microphone"></i></button>
        <button id="pauseButton<?= $thepost.$target ?>" type="button" class="pauseButton btn btn-secondary" style="display: none;"><i class="fas fa-pause-circle"></i></button> <!-- Pause button -->
    </div>
<?php else: ?>

    <textarea id="caption<?= $thepost.$target ?>" class="hidden-input"></textarea>

    <style>
        .reply-quoted { display: none; }
        .reply { color: var(--gray) !important; font-size: smaller; }
        .emojiPickerIcon { display: none !important; }
    </style>
    
<?php endif; ?>

</form>


<style>
    div.emojiBox { position: absolute; left: 0; right: 0; top: -150vh; margin: auto; background-color:red; z-index: 999; }
    div.emojiPicker { position: absolute !important; left: 0 !important; bottom: -100% !important; }
    .preview-item { position: relative; width: 25%; height: 100%; display: inline-block; background-size: cover; background-position: center; background-repeat: no-repeat; }
    .preview-item .remove-btn { position: absolute; top: 2px; right: 2px; background: rgba(0,0,0,0.5); color: white; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; line-height: 1; pointer-events: auto; cursor: pointer; }
    .preview-item .progress-bar { position: absolute; bottom: 0; left: 0; height: 3px; background: #4CAF50; width: 0; }
    .chat-box label[for=media<?= $thepost.$target ?>] { margin-top: .75em; padding: .25em; border: 0; }
    #deleteButton<?= $thepost.$target ?> { position: absolute; bottom: 0; left: 0; width: 3em; height: 3em; padding: 1em; margin: 1em; z-index: 99999; border-radius: 50%; }
    #voicenoteArea<?= $thepost.$target ?> { position: absolute; }
    #voicenoteArea<?= $thepost.$target ?> * { max-width: 100%; }
    #vnTimer<?= $thepost.$target ?>, #pausedText<?= $thepost.$target ?> { position: absolute; bottom: 0; left: 0; width: 3em; line-height: 3em; margin: 1em; z-index: 99999; border-radius: 50%; font-weight: bold; }
    #oscilloscopeCanvas<?= $thepost.$target ?> { width: 100%; height: 100%; }
    #oscilloscopeContainer<?= $thepost.$target ?> { position: absolute; bottom: 0; margin-bottom: -5em; left: 0; right: 0; height: 5em; }
    #timeline<?= $thepost.$target ?> { height: 4em !important; }
</style>

<script>
    var isUploading = false;
    var isRecording = false; // Track recording state
    var mediaRecorder; // MediaRecorder instance
    var audioChunks = []; // Store audio chunks
    var isDeleted = false; // Declare isDeleted here
    var mediaStream; // Store the media stream
    var timerInterval; // Timer interval variable
    var recordingStartTime; // Start time for the recording
    var elapsedTime = 0; // Track elapsed time for the timer
    var uploadedFilesCount = 0; // Initialize the counter for uploaded files
    
    $("#caption<?= $thepost.$target ?>").keyup(function() {
        // Encode the cookie name and value to ensure special characters are properly handled
        document.cookie = encodeURIComponent('<?php echo $target ?>') + "=" + encodeURIComponent(this.value) + "; path=/";
    });
    
    $(document).ready(function() {
        $('#caption<?= $thepost.$target ?>').emojiPicker({
            position: 'center',
            width: '100%',
            container: 'div.emojiBox',
            iconBackgroundColor: 'unset',
        });
    
        var formData = new FormData(); // Initialize formData globally
        // Initialize uploadQueue to store both file and XMLHttpRequest
        var uploadQueue = []; // Array to hold objects with file and xhr
        var previewCount = 0; // Initialize previewCount globally
        var isUploading = false; // Initialize isUploading globally
    
        // Function to update form data (added this function)
        function updateFormData() {
            var formData = new FormData($('form')[0]);
            // You can add any additional data to formData here if needed
        }
    
        // Initially hide the send button and show the microphone button
        $('#sendMessage<?= $thepost.$target ?>').hide();
        $('#voiceNoteBtn<?= $thepost.$target ?>').show();
    
        // Function to check the state of the send button
        function checkSendButtonState() {
            var textContent = $('#caption<?= $thepost.$target ?>').val().trim();
            var hasMedia = uploadedFilesCount > 0;

            // Show send button if uploading, if there's text, or if there are files in the preview area
            $('#sendMessage<?= $thepost.$target ?>').toggle(isUploading || textContent.length > 0 || hasMedia);
            $('#voiceNoteBtn<?= $thepost.$target ?>').toggle(!isUploading && textContent.length === 0 && !hasMedia);
        }
    
        // Call checkSendButtonState on input changes
        $('#messageForm<?= $thepost.$target ?>').on('input change', function() {
            checkSendButtonState(); // Check the send button state
            updateSendButton();
        });
    
        // Handle file input change
        $('#media<?= $thepost.$target ?>').on('change', function(e) {
            var files = e.target.files; // Store the files from the input
            var preview = $('#previeewArea<?= $thepost.$target ?>');
            var maxSize = $(this).data('max-size') || 41943040; // 40MB in bytes

            // Clear the upload queue and preview before adding new files
            if (uploadedFilesCount + files.length > 4) {
                alert('You can only select up to 4 files in total.');
                $(this).val(''); // Clear the file input
                return;
            }

            // Proceed with adding files to the upload queue
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                if (file.size > maxSize) {
                    alert(file.name + ' is too large. Maximum file size is 40MB.');
                    continue;
                }
                previewFile(file, preview);
                uploadQueue.push({ file: file }); // Add the new files to the upload queue
                uploadedFilesCount++; // Increment the uploaded files count
            }

            // Start uploading files if not already uploading
            if (!isUploading && uploadQueue.length > 0) {
                uploadNextFile(); // Start uploading without passing files
            }
        
            // Update formData after file selection
            updateFormData();
        
            // Clear the file input
            $(this).val('');
        
            // Call checkSendButtonState after file selection
            checkSendButtonState(); // Ensure the send button state is updated
        });
    
        function previewFile(file, preview) {
            createThumbnail(file, function(thumbnailBlob) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var div = $('<div>').addClass('preview-item');
                
                    // Set background image based on file type
                    if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
                        div.css('background-image', 'url(' + e.target.result + ')');
                    } else if (file.type === 'application/pdf') {
                        div.css('background-image', 'url(../../img/pdf.jpeg)');
                    } else {
                        div.css('background-image', 'url(../../img/file-icon.png)');
                    }
                
                    var nameOverlay = $('<div>').text(file.name).css({
                        'position': 'absolute',
                        'bottom': '3px',
                        'left': '0',
                        'right': '0',
                        'background-color': 'rgba(0,0,0,0.5)',
                        'color': 'white',
                        'padding': '2px',
                        'font-size': '10px',
                        'text-overflow': 'ellipsis',
                        'overflow': 'hidden',
                        'white-space': 'nowrap'
                    });
                
                    // Create a progress bar element
                    var progressBar = $('<div class="progress-bar"></div>').css({
                        'height': '3px',
                        'background': '#4CAF50',
                        'width': '0%'
                    });
                
                    // Create a remove button
                    var removeBtn = $('<button>')
                        .attr('id', 'removeBtn_<?= $thepost.$target ?>_' + encodeURIComponent(file.name)) // Unique ID for the remove button
                        .addClass('remove-btn')
                        .text('×')
                        .on('click', function(e) {
                            e.preventDefault();
                            div.remove(); // Remove the preview item

                            // Check if the file is currently in the upload queue
                            var index = uploadQueue.findIndex(item => item.file === file); // Get the index of the file in the upload queue
                            if (index > -1) {
                                // If the file is currently being uploaded, abort the upload
                                if (uploadQueue[index].xhr) { // Ensure xhr exists
                                    uploadQueue[index].xhr.abort(); // Abort the XMLHttpRequest
                                }
                                uploadQueue.splice(index, 1); // Remove the file from the upload queue
                            }

                            // Find the index of the file in the uploaded_files[] inputs
                            var uploadedFiles = $('input[name="uploaded_files[]"]');
                            var uploadedIndex = uploadedFiles.index(uploadedFiles.filter(function() {
                                return $(this).val() === file.uploadedPath; // Match the value of the input
                            }));

                            // Remove the file from the uploaded files inputs
                            if (uploadedIndex > -1) {
                                uploadedFiles.eq(uploadedIndex).remove();
                                $('input[name="media_types[]"]').eq(uploadedIndex).remove();
                                $('input[name="original_filenames[]"]').eq(uploadedIndex).remove();
                                $('input[name="media_sizes[]"]').eq(uploadedIndex).remove();
                            }

                            uploadedFilesCount--; // Decrement the uploaded files count

                            // Update the send button state
                            checkSendButtonState(); // Ensure the send button state is updated after removal
                            updateSendButton();

                            // Call uploadNextFile to continue uploading any remaining files
                            uploadNextFile(); // Ensure that the next file in the queue is uploaded
                        });
                    
                    div.append(nameOverlay, progressBar, removeBtn);
                    preview.append(div);
                };
            
                if (thumbnailBlob) {
                    reader.readAsDataURL(thumbnailBlob);
                } else {
                    reader.readAsDataURL(file);
                }
            });
        }
    
        // Function to upload the next file in the queue
        function uploadNextFile() {
            if (uploadQueue.length === 0) {
                isUploading = false;
                checkSendButtonState(); // Update send button state
                return;
            }

            isUploading = true;
            checkSendButtonState(); // Update send button state

            var fileObj = uploadQueue[0]; // Get the next file object to upload
            var file = fileObj.file; // Extract the file from the object

            // Check if the file is still in the upload queue before proceeding
            if (!uploadQueue.includes(fileObj)) {
                uploadQueue.shift(); // Remove the file from the queue if it was removed
                uploadNextFile(); // Call again to upload the next file
                return; // Exit the function
            }

            var uploadData = new FormData(); // Create a new FormData instance for the upload
            uploadData.append('media', file);

            // Append the original filename
            uploadData.append('original_filename', file.name); // Ensure this line is present

            // Determine media type and size
            var type = file.type.startsWith('image/') ? 'image' :
                file.type.startsWith('video/') ? 'video' : 'attachment';
            uploadData.append('type', type);
            uploadData.append('media_size', file.size); // Add media size

            // Include the caption if this is the first file
            var caption = $('#caption<?= $thepost.$target ?>').val().trim();
            uploadData.append('upload_context', 'chat');
            uploadData.append('caption', caption);

            // Create a thumbnail for the file
            createThumbnail(file, function(thumbnailBlob) {
                if (thumbnailBlob) {
                    uploadData.append('thumbnail', thumbnailBlob, 'thumbnail_' + file.name); // Append thumbnail to FormData
                }

                // AJAX request to upload the file
                var xhr = $.ajax({
                    url: 'upload_media.php',
                    type: 'POST',
                    data: uploadData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        // Upload progress event
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total;
                                // Update the progress bar for the current file
                                var progressBar = $('.preview-item').last().find('.progress-bar');
                                progressBar.css('width', percentComplete * 100 + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        // Handle successful upload
                        var uploadedFile = JSON.parse(response);

                        if (uploadedFile.success) {
                            file.uploadedPath = uploadedFile.path;
                            file.mediaType = type;

                            $('<input>').attr({
                                type: 'hidden',
                                name: 'uploaded_files[]',
                                value: uploadedFile.path
                            }).appendTo('form');

                            // Append media type and size to the uploaded_files array
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'media_types[]',
                                value: type
                            }).appendTo('form');
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'media_sizes[]',
                                value: file.size
                            }).appendTo('form');
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'original_filenames[]',
                                value: file.name
                            }).appendTo('form');

                            uploadQueue.shift(); // Remove the uploaded file from the queue
                            uploadNextFile(); // Upload the next file
                        } else {
                            console.error('Error uploading file:', uploadedFile.error);
                            uploadQueue.shift(); // Remove the failed file from the queue
                            uploadNextFile(); // Attempt to upload the next file
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error uploading file:', file.name, error);
                        uploadQueue.shift(); // Remove the failed file from the queue
                        uploadNextFile(); // Attempt to upload the next file
                    },
                    complete: function() {
                        // Check if all uploads are complete
                        if (uploadQueue.length === 0) {
                            isUploading = false;
                            checkSendButtonState(); // Re-enable the send button
                            updateSendButton();
                        }
                    }
                });

                // Store the XMLHttpRequest in the uploadQueue
                uploadQueue[0].xhr = xhr; // Store the xhr object with the file object
            });
        }
    
        function createThumbnail(file, callback) {
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = new Image();
                    img.onload = function() {
                        var canvas = document.createElement('canvas');
                        var ctx = canvas.getContext('2d');
                        var ratio = Math.min(720 / img.width, 720 / img.height);
                        canvas.width = img.width * ratio;
                        canvas.height = img.height * ratio;
                        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                        canvas.toBlob(callback, 'image/jpeg', 0.8);
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else if (file.type.startsWith('video/')) {
                var video = document.createElement('video');
                video.onloadedmetadata = function() {
                    video.currentTime = 0;
                };
                video.onseeked = function() {
                    var canvas = document.createElement('canvas');
                    var ctx = canvas.getContext('2d');
                    var ratio = Math.min(720 / video.videoWidth, 720 / video.videoHeight);
                    canvas.width = video.videoWidth * ratio;
                    canvas.height = video.videoHeight * ratio;
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    canvas.toBlob(callback, 'image/jpeg', 0.8);
                    URL.revokeObjectURL(video.src);
                };
                video.src = URL.createObjectURL(file);
            } else {
                callback(null);
            }
        }
    
        // Function to validate input
        function validateInput() {
            var textContent = $('#caption<?= $thepost.$target ?>').val().trim();
            // var hasMedia = uploadQueue.length > 0; // Check if there are files in the upload queue
            var maxTextLength = 500; // Set a maximum character limit for the text input
            // var hasMedia = $('input[name="uploaded_files[]"]').length > 0;
            var hasMedia = uploadedFilesCount > 0;
        
            // Reset visual feedback
            $('#caption<?= $thepost.$target ?>').css('border-color', ''); // Reset border color
            $('#sendMessage<?= $thepost.$target ?>').prop('disabled', false); // Enable the send button
        
            // Check if the text area is empty and there are no media files
            if (textContent.length === 0 && !hasMedia) {
                $('#caption<?= $thepost.$target ?>').css('border-color', 'red'); // Change border color to red
                $('#sendMessage<?= $thepost.$target ?>').prop('disabled', true); // Disable the send button
                //alert('You cannot send an empty message. Please enter some text or select a file to upload.'); // Optional alert
                //return false; // Invalid input
            }
        
            if (hasMedia) {
                $('#sendMessage<?= $thepost.$target ?>').prop('disabled', false); // Disable the send button
            }
        
            // Check for maximum character limit
            if (textContent.length > maxTextLength) {
                $('#caption<?= $thepost.$target ?>').css('border-color', 'red'); // Change border color to red
                $('#sendMessage<?= $thepost.$target ?>').prop('disabled', true); // Disable the send button
                alert('Your message is too long. Please limit it to ' + maxTextLength + ' characters.'); // Optional alert
                return false; // Invalid input
            }
        
            // If we reach here, the input is valid
            return true; // Valid input
        }
    
        // Function to handle send button click
        $('#sendMessage<?= $thepost.$target ?>').on('click', function(e) {
            e.preventDefault();
             
            // Clear the text area and reset input fields immediately
            $('#media<?= $thepost.$target ?>').val(''); // Clear the file input
            uploadedFilesCount = 0; // Reset uploaded files count
            $('#previeewArea<?= $thepost.$target ?>').empty(); // Clear any existing media previews
             
            // Disable the send button to prevent multiple clicks
            $(this).prop('disabled', true); // Disable to prevent accidental double clicks
             
            // Abort all ongoing uploads
            uploadQueue.forEach(item => {
                if (item.xhr) {
                    item.xhr.abort(); // Abort the XMLHttpRequest
                }
            });
             
            // Clear the upload queue
            uploadQueue = []; // Clear the upload queue to prevent further uploads

            // Disable the send button to prevent multiple clicks
            $(this).prop('disabled', true); // Disable to prevent accidental double clicks
            $(".reply-quoted").collapse('hide');
            document.cookie = encodeURIComponent('<?php echo $target ?>') + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
             
            // Set states to false
            isUploading = false; // Reset uploading state
            isRecording = false; // Reset recording state
             
            // Create a new FormData instance from the form
            var formData = new FormData($('form')[0]); // Assuming the form contains other necessary fields
            formData.append('action', 'send_message'); // Append action to formData
            // Append other necessary fields
            var reply = $('#reply<?= $thepost.$target ?>').val().trim();
            var target = $('#target<?= $thepost.$target ?>').val().trim();
            var caption = $('#caption<?= $thepost.$target ?>').val().trim();
            $('#caption<?= $thepost.$target ?>').val(''); // Clear the textarea
            formData.append('target', target);
            formData.append('reply', reply);
            formData.append('caption', caption);

            // Gather only the files that are still visible in the preview area
            $('#previeewArea<?= $thepost.$target ?> .preview-item').each(function() {
                var fileName = $(this).find('.remove-btn').attr('id').split('_').pop(); // Extract the file name from the remove button ID
                var index = $('input[name="original_filenames[]"]').index($('input[name="original_filenames[]"]').filter(function() {
                    return $(this).val() === fileName; // Match the filename
                }));

                if (index > -1) {
                    // Append the corresponding file attributes using the index
                    formData.append('uploaded_files[]', $('input[name="uploaded_files[]"]').eq(index).val()); // Append the file path
                    formData.append('media_types[]', $('input[name="media_types[]"]').eq(index).val()); // Append media type
                    formData.append('media_sizes[]', $('input[name="media_sizes[]"]').eq(index).val()); // Append media size
                    formData.append('original_filenames[]', $('input[name="original_filenames[]"]').eq(index).val()); // Append original filename
                }
            });

            // Send the AJAX request
            $.ajax({
                url: 'action.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // console.log('Message sent successfully:', response);
                    // send this response to the web socket
                    $('#caption<?= $thepost.$target ?>').val(''); // Clear the textarea
                    $('#caption<?= $thepost.$target ?>, #media<?= $thepost.$target ?>, label[for=media<?= $thepost.$target ?>], .emojiPickerIcon').css({ opacity: 1, pointerEvents: 'auto' });
                    $('#audio-<?= $thepost.$target ?>').attr('src', '').hide(); // Clear audio preview
                    $('#audio-controls<?= $thepost.$target ?>').hide(); // Clear audio preview
                    $('#oscilloscopeContainer<?= $thepost.$target ?>').hide(); // Hide oscilloscope
                    $('#voiceNoteBtn<?= $thepost.$target ?>').show(); // Show voice note button again
                    $('#sendMessage<?= $thepost.$target ?>').hide(); // Hide send button
                    $('#deleteButton<?= $thepost.$target ?>').hide(); // Hide delete button
                    audioToSend = null; // Reset the audio to send

                    // Clear all previous media inputs
                    $('input[name="uploaded_files[]"]').remove(); // Remove uploaded files input
                    $('input[name="media_types[]"]').remove(); // Remove media types input
                    $('input[name="original_filenames[]"]').remove(); // Remove original filenames input
                    $('input[name="media_sizes[]"]').remove(); // Remove media sizes input
                    uploadedFilesCount = 0; // Reset uploaded files count
                    
                    // Check the send button state
                    checkSendButtonState(); // Check the send button state after sending
                },
                error: function(xhr, status, error) {
                    console.error('Error sending message:', error);
                },
                complete: function() {
                    // Re-enable the send button after the request is complete
                    checkSendButtonState(); // Check the send button state after sending
                }
            });
        });
    
        $("#caption<?= $thepost.$target ?>").on('keyup input', function() {
            document.cookie = encodeURIComponent('<?php echo $target ?>') + "=" + encodeURIComponent(this.value) + "; path=/";
            updateSendButton();
            checkSendButtonState();
        });
    
        function updateSendButton() {
            var sendButton = $('#sendMessage<?= $thepost.$target ?>');
            if (!canSendMessage() || isUploading) {
                sendButton.prop('disabled', true);
                sendButton.css('opacity', '0.5');
            } else {
                sendButton.prop('disabled', false);
                sendButton.css('opacity', '1');
            }
        }
    
        function canSendMessage() {
            var textContent = $('#caption<?= $thepost.$target ?>').val().trim();
            var hasMedia = uploadedFilesCount > 0;
            var allUploadsComplete = uploadQueue.length === 0; // Check if all uploads are complete
            // var hasMedia = $('input[name="uploaded_files[]"]').length > 0;
            return textContent.length > 0 || hasMedia && !isUploading && allUploadsComplete;
        }
    
        updateSendButton();
    
        // Check if there are any files in the upload queue on document load
        // var currentUploadedFilesCount = $('input[name="uploaded_files[]"]').length; // Get the count of already uploaded files
        if (uploadedFilesCount > 0) {
            console.log('There are still files in the upload queue.'); // Log message when files are present
            // Check if the uploaded files already have 4 files
        
            // Add a soft bootstrap alert
            $('<div class="alert alert-info alert-dismissible fade show" style="z-index:999999999" role="alert">All previous file selections have been cleared.</div>')
                .appendTo('body') // Adjust the target as needed
                .delay(1000) // Show for 1 second
                .fadeOut(500, function() { $(this).remove(); }); // Fade out and remove

            // Iterate through the uploaded files and show previews
            $('input[name="uploaded_files[]"]').remove(); // Remove uploaded files input
            $('input[name="media_types[]"]').remove(); // Remove media types input
            $('input[name="original_filenames[]"]').remove(); // Remove original filenames input
            $('input[name="media_sizes[]"]').remove(); // Remove media sizes input
            $('#media<?= $thepost.$target ?>').val(''); // Clear the file input
        
            // Update the send button state
            checkSendButtonState(); // Update send button state
        }
    
        // Add a paste event listener to the caption textarea
        $("#caption<?= $thepost.$target ?>").on('paste', function(e) {
            var items = (e.originalEvent || e).clipboardData.items;
            for (var i = 0; i < items.length; i++) {
                if (items[i].kind === 'file') {
                    var file = items[i].getAsFile();
                    if (file) {
                        // Check file size and type if necessary
                        var maxSize = $('#media<?= $thepost.$target ?>').data('max-size') || 41943040; // 40MB
                        if (file.size > maxSize) {
                            alert(file.name + ' is too large. Maximum file size is 40MB.');
                            continue;
                        }
                        // Call the function to preview the file
                        previewFile(file, $('#previeewArea<?= $thepost.$target ?>'));
                        uploadQueue.push({ file: file }); // Add the file to the upload queue
                        checkSendButtonState(); // Update the send button state
                    }
                }
            }
            uploadNextFile();
            updateFormData();
            checkSendButtonState();
        });
    
        // Function to start the timer
        function startTimer() {
            $('#vnTimer<?= $thepost.$target ?>').text(formatTime(elapsedTime)).show(); // Show the timer

            timerInterval = setInterval(function() {
                elapsedTime++; // Increment elapsed time
                $('#vnTimer<?= $thepost.$target ?>').text(formatTime(elapsedTime)); // Update timer display
            }, 1000);
        }

        // Function to stop the timer
        function stopTimer() {
            clearInterval(timerInterval);
            $('#vnTimer<?= $thepost.$target ?>').hide(); // Hide the timer
        }

        // Function to format elapsed time into MM:SS
        function formatTime(seconds) {
            var minutes = Math.floor(seconds / 60);
            var secs = seconds % 60;
            return (minutes < 10 ? '0' : '') + minutes + ':' + (secs < 10 ? '0' : '') + secs;
        }

        // Function to handle voice note button click
        $('#voiceNoteBtn<?= $thepost.$target ?>').on('click', function() {
            if (!isRecording) {
                // Start recording
                isRecording = true;
                startTimer(); // Start the timer
                $('#caption<?= $thepost.$target ?>, #media<?= $thepost.$target ?>, label[for=media<?= $thepost.$target ?>], .emojiPickerIcon').css({ opacity: 0, pointerEvents: 'none' });
                $(this).html('<i class="fas fa-stop"></i>'); // Change to stop button
                $('#pauseButton<?= $thepost.$target ?>').html('<i class="fas fa-pause-circle"></i>').show(); // Show the pause button
                $('#pausedText<?= $thepost.$target ?>').hide(); // Show the pause button

                if(currentlyPlayingAudio) {
                    currentlyPlayingAudio.pause();
                    // currentlyPlayingAudio.currentTime = 0; // Reset to the beginning
                    var previousPlayButton = document.getElementById('play-btn' + currentlyPlayingAudio.id.split('-')[1]);
                    $(previousPlayButton).html('<i class="fas fa-play"></i>'); // Reset play button for the previous audio
                }

                // Show oscilloscope while recording
                $('#oscilloscopeContainer<?= $thepost.$target ?>').show(); // Show oscilloscope

                // Start audio recording
                navigator.mediaDevices.getUserMedia({ audio: true })
                    .then(function(stream) {
                        mediaStream = stream; // Store the media stream
                        mediaRecorder = new MediaRecorder(stream);
                        mediaRecorder.start(); // Start the media recorder

                        // Set up the audio context and analyser for the oscilloscope
                        var audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        var analyser = audioContext.createAnalyser();
                        var source = audioContext.createMediaStreamSource(stream);
                        source.connect(analyser);
                        analyser.fftSize = 2048;

                        var bufferLength = analyser.frequencyBinCount;
                        var dataArray = new Uint8Array(bufferLength);
                        var canvas = document.getElementById('oscilloscopeCanvas<?= $thepost.$target ?>');
                        var canvasCtx = canvas.getContext('2d');

                        function draw() {
                            requestAnimationFrame(draw);
                            analyser.getByteTimeDomainData(dataArray);
                            canvasCtx.fillStyle = 'rgb(205, 205, 205)';
                            canvasCtx.fillRect(0, 0, canvas.width, canvas.height);
                            canvasCtx.lineWidth = 2;
                            canvasCtx.strokeStyle = 'rgb(0, 0, 0)';
                            canvasCtx.beginPath();
                            var sliceWidth = canvas.width * 1.0 / bufferLength;
                            var x = 0;

                            for (var i = 0; i < bufferLength; i++) {
                                var v = dataArray[i] / 128.0;
                                var y = v * canvas.height / 2;
                                if (i === 0) {
                                    canvasCtx.moveTo(x, y);
                                } else {
                                    canvasCtx.lineTo(x, y);
                                }
                                x += sliceWidth;
                            }
                            canvasCtx.lineTo(canvas.width, canvas.height / 2);
                            canvasCtx.stroke();
                        }

                        // Start drawing the oscilloscope
                        draw();

                        // Inside the mediaRecorder.ondataavailable function
                        mediaRecorder.ondataavailable = function(event) {
                            audioChunks.push(event.data);
                        };

                        // Function to handle the stop of recording
                        mediaRecorder.onstop = function() {
                            console.log("Recording stopped. isDeleted:", isDeleted); // Debugging statement
                            if (isDeleted) { // Check if isDeleted is true
                                console.log("Upload process halted due to deletion."); // Debugging statement
                                return; // Stop further processing if deleted
                            }

                            const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                            audioChunks = []; // Reset chunks

                            // Create a temporary URL for the audio blob
                            const audioURL = URL.createObjectURL(audioBlob);
                            
                            // Show the audio preview immediately
                            $('#audio-<?= $thepost.$target ?>').attr('src', audioURL).show(); // Show audio preview
                            $('#audio-controls<?= $thepost.$target ?>').show(); // Show audio preview
                            $('#oscilloscopeContainer<?= $thepost.$target ?>').hide(); // Hide oscilloscope after recording
                            
                            // Hide the microphone button and show the send button
                            $('#voiceNoteBtn<?= $thepost.$target ?>').hide(); // Hide microphone button
                            $('#sendMessage<?= $thepost.$target ?>').show(); // Show send button
                            
                            // Show the delete button after recording is complete
                            $('#deleteButton<?= $thepost.$target ?>').show(); // Show delete button
                            
                            // Start the upload process
                            if (!isDeleted) { // Check again before starting the upload
                                isUploading = true; // Set uploading state
                                uploadVoicenotes(audioBlob); // Start upload
                            }
                            
                            audioToSend = audioBlob; // Store the audio blob globally
                            
                            // Reset audioToSend after handling
                            audioToSend = null; 
                            stopTimer(); // Stop the timer when recording stops
                            stream.getTracks().forEach(track => track.stop()); // Stop all tracks of the media stream
                            mediaStream = null; // Clear the media stream reference
                            
                            // Reset elapsed time for the next recording
                            elapsedTime = 0; 
                            $('#pauseButton<?= $thepost.$target ?>').hide(); // Hide the pause button after recording

                            // Calculate the size of the audio blob in KB
                            const audioSize = (audioBlob.size / 1024).toFixed(2); // Size in KB
                            // const audioLength = formatTime(elapsedTime); // Format the elapsed time

                            // Update the length and size display
                            $('#vn-length-value<?= $thepost.$target ?>').text($('#vnTimer<?= $thepost.$target ?>').text()); // Use the last time from the vn timer
                            $('#vn-size-value<?= $thepost.$target ?>').text(audioSize + ' KB'); // Update size


                        };
                    })
                    .catch(function(err) {
                        console.error('Error accessing microphone:', err);
                    });
            } else {
                // Stop recording
                isRecording = false;
                mediaRecorder.stop();
                $(this).html('<i class="fas fa-microphone"></i>'); // Change back to microphone button
                $('#pauseButton<?= $thepost.$target ?>').hide(); // Hide the pause button
                $('#pausedText<?= $thepost.$target ?>').hide();
            }
        });

        // Function to handle pause button click
        $('#pauseButton<?= $thepost.$target ?>').on('click', function() {
            if (mediaRecorder && mediaRecorder.state === "recording") {
                mediaRecorder.pause(); // Pause the recording
                $(this).html('<i class="fas fa-play-circle text-danger"></i>'); // Change to play button
                stopTimer(); // Pause the timer
                $('#pausedText<?= $thepost.$target ?>').show();
            } else if (mediaRecorder && mediaRecorder.state === "paused") {
                mediaRecorder.resume(); // Resume the recording
                $(this).html('<i class="fas fa-pause-circle"></i>'); // Change back to pause button
                startTimer(); // Resume the timer from where it left off
                $('#pausedText<?= $thepost.$target ?>').hide();
                if(currentlyPlayingAudio) {
                    currentlyPlayingAudio.pause();
                    // currentlyPlayingAudio.currentTime = 0; // Reset to the beginning
                    var previousPlayButton = document.getElementById('play-btn' + currentlyPlayingAudio.id.split('-')[1]);
                    $(previousPlayButton).html('<i class="fas fa-play"></i>'); // Reset play button for the previous audio
                }
            }
        });

        // Function to handle delete button click
        $('#deleteButton<?= $thepost.$target ?>').on('click', function() {
            isDeleted = true; // Set isDeleted to true when delete button is clicked
            console.log("Delete button clicked. isDeleted set to true."); // Debugging statement

            // Stop recording if it is active
            if (isRecording) {
                mediaRecorder.stop(); // Stop the recording
                isRecording = false; // Update the recording state
            }

            // Clear the audio preview and hide the oscilloscope
            $('#audio-<?= $thepost.$target ?>').attr('src', '').hide(); // Clear audio preview
            $('#audio-controls<?= $thepost.$target ?>').hide(); // Clear audio preview
            $('#oscilloscopeContainer<?= $thepost.$target ?>').hide(); // Hide oscilloscope

            // Reset the audio to send
            audioToSend = null; 

            // Reset the UI elements
            $('#caption<?= $thepost.$target ?>').val(''); // Clear the textarea
            $('#caption<?= $thepost.$target ?>, #media<?= $thepost.$target ?>, label[for=media<?= $thepost.$target ?>], .emojiPickerIcon').css({ opacity: 1, pointerEvents: 'auto' });

            // Hide the delete button
            $(this).hide(); // Hide delete button

            // Show the voice note button and revert its icon
            $('#voiceNoteBtn<?= $thepost.$target ?>').show().html('<i class="fas fa-microphone"></i>'); // Show voice note button again and revert icon
            $('#sendMessage<?= $thepost.$target ?>').hide(); // Hide send button

            // Reset isDeleted to false after handling the delete action
            isDeleted = false; // Reset isDeleted to allow future actions

            $('input[name="uploaded_files[]"]').remove(); // Remove uploaded files input
            $('input[name="media_types[]"]').remove(); // Remove media types input
            $('input[name="original_filenames[]"]').remove(); // Remove original filenames input
            $('input[name="media_sizes[]"]').remove(); // Remove media sizes input
            $('#media<?= $thepost.$target ?>').val(''); // Clear the file input

            // Reset elapsed time for the next recording
            elapsedTime = 0; 
            $('#pauseButton<?= $thepost.$target ?>').hide(); // Hide the pause button after deletion
        });

        // Function to upload voice notes
        function uploadVoicenotes(audioBlob) {
            var uploadData = new FormData();
            uploadData.append('media', audioBlob, 'voicenote.wav'); // Ensure the filename has the correct extension
            uploadData.append('type', 'voicenote');
            uploadData.append('media_size', audioBlob.size); // Add media size
            uploadData.append('upload_context', 'chat'); // Add upload context

            // AJAX request to upload the file
            $.ajax({
                url: 'upload_media.php',
                type: 'POST',
                data: uploadData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            $('#progressBar<?= $thepost.$target ?>').css('width', percentComplete * 100 + '%'); // Update progress bar
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    // Handle successful upload
                    console.log('Voice note uploaded successfully:', response);
                    var uploadedFile = JSON.parse(response);
                    var savingTime = $('#vnTimer<?= $thepost.$target ?>').text();
                    if (uploadedFile.success) {
                        file.uploadedPath = uploadedFile.path;
                        file.mediaType = 'voicenote';

                        uploadedFilesCount++;
                    
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'uploaded_files[]',
                            value: uploadedFile.path
                        }).appendTo('form');
                    
                        // Append media type and size to the uploaded_files array
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'media_types[]',
                            value: 'voicenote'
                        }).appendTo('form');
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'media_sizes[]',
                            value: audioBlob.size
                        }).appendTo('form');
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'original_filenames[]',
                            value: savingTime
                        }).appendTo('form');
                    
                        uploadQueue.shift(); // Remove the uploaded file from the queue

                        var sendButton = $('#sendMessage<?= $thepost.$target ?>');
                        
                        sendButton.prop('disabled', false);
                        sendButton.css('opacity', '1');

                        // Show the audio preview
                        $('#audio-<?= $thepost.$target ?>').attr('src', uploadedFile.path).show(); // Show audio preview
                        $('#audio-controls<?= $thepost.$target ?>').show(); // Show audio preview
                        $('#progressContainer<?= $thepost.$target ?>').hide(); // Hide the progress bar after upload
                    } else {
                        console.error('Error uploading voice note:', uploadedFile.error);
                    }
                    isUploading = false; // Reset uploading state
                    checkSendButtonState(); // Update send button state
                    updateFormData();
                    
                },
                error: function(xhr, status, error) {
                    console.error('Error uploading voice note:', error);
                    isUploading = false; // Reset uploading state
                }
            });
        }
    });
    
</script>