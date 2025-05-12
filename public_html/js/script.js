new WOW().init();

$.fancybox.defaults.autoFocus = false; // Disable autofocus
$.fancybox.defaults.touch = false; // Disable touch/swipe interactions
$.fancybox.defaults.keyboard = false; // Disable keyboard navigation
$.fancybox.defaults.loop = false; // Disable looping through slides
$.fancybox.defaults.autoPlay = false; // Disable autoplay for audio globally
$.fancybox.defaults.slideShow = false; // Disable slideshow

// Ensure all audio elements are paused when Fancybox opens

 
var currentlyPlayingAudio = null; // Variable to track the currently playing audio

function togglePlay(id) {
    var audio = document.getElementById('audio-' + id);
    var playButton = document.getElementById('play-btn' + id);

    // Check if the audio element exists
    if (!audio) {
        console.error('Audio element not found for ID:', id);
        return; // Exit the function if the audio element is not found
    }

    // Check if the mediaRecorder is recording
    if (mediaRecorder && mediaRecorder.state === "recording") {
        mediaRecorder.pause(); // Pause the recording
        $(this).html('<i class="fas fa-play-circle text-danger"></i>'); // Change to play button
        clearInterval(timerInterval);
        $('.pausedText').show();
        $('.pauseButton').html('<i class="fas fa-play-circle text-danger"></i>');
        $('.vnTimer').hide();
    }

    // If there is another audio playing, pause it
    if (currentlyPlayingAudio && currentlyPlayingAudio !== audio) {
        currentlyPlayingAudio.pause();
        var previousPlayButton = document.getElementById('play-btn' + currentlyPlayingAudio.id.split('-')[1]);
        $(previousPlayButton).html('<i class="fas fa-play"></i>'); // Reset play button for the previous audio
    }

    // Play or pause the current audio
    if (audio.paused) {
        audio.play().then(function() {
            currentlyPlayingAudio = audio; // Set the currently playing audio
            $(playButton).html('<i class="fas fa-pause"></i>'); // Change play button to pause
            updateProgress(id); // Update progress for the current audio
        }).catch(function(error) {
            console.error('Error trying to play audio:', error);
        });
    } else {
        audio.pause();
        $(playButton).html('<i class="fas fa-play"></i>'); // Change play button to play
        currentlyPlayingAudio = null; // Clear the currently playing audio
    }
}

function toggleSpeed(id) {
    var audio = document.getElementById('audio-' + id);
    var speedDisplay = document.getElementById('vn-speed' + id);
    var currentSpeed = parseFloat(speedDisplay.innerText.slice(1));
    
    // Toggle playback speed
    if (currentSpeed === 1) {
        audio.playbackRate = 1.5;
        speedDisplay.innerText = 'x1.5';
    } else if (currentSpeed === 1.5) {
        audio.playbackRate = 2;
        speedDisplay.innerText = 'x2';
    } else {
        audio.playbackRate = 1;
        speedDisplay.innerText = 'x1';
    }
}

function updateProgress(id) {
    var audio = document.getElementById('audio-' + id);
    var playButton = document.getElementById('play-btn' + id);
    var seek = document.getElementById('progress-timeline' + id);
    var progress = document.getElementById('progress-inner' + id);
    var timer = document.getElementById('countdown-timer' + id);

    audio.addEventListener('timeupdate', function() {
        if (audio.duration > 0) { // Check if duration is valid
            var percentage = (audio.currentTime / audio.duration) * 100;
            progress.style.width = percentage + '%';
            timer.innerHTML = formatTime(audio.currentTime);
        }
    });

    audio.addEventListener('ended', function() {
        progress.style.width = '100%'; // Ensure progress is full when audio ends
        timer.innerHTML = formatTime(audio.duration); // Show total duration when finished
        $(playButton).html('<i class="fas fa-play"></i>'); // Reset play button to play icon
        currentlyPlayingAudio = null; // Clear the currently playing audio
        progress.style.width = 0 + '%';
        timer.innerHTML = '00:00';

    });

    // Ensure the audio is ready before allowing seeking
    audio.addEventListener('loadedmetadata', function() {
        console.log('Audio is ready. Duration:', audio.duration);
    });

    seek.addEventListener('click', function(event) {
        var rect = seek.getBoundingClientRect();
        var offsetX = event.clientX - rect.left; // Get the click position
        var totalWidth = rect.width;
        var percentage = offsetX / totalWidth; // Calculate the percentage
        
        // Set the audio current time
        audio.currentTime = percentage * audio.duration; // Set the audio current time
        
    });
}

function formatTime(seconds) {
    var minutes = Math.floor(seconds / 60);
    seconds = Math.floor(seconds % 60);
    return (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
}



// Initialize Fancybox for all elements with data-fancybox attribute
$("[data-fancybox]").fancybox();

function openQRCodeModal(inputId, profileSpec, dat) {

  // console.log("Input ID:", inputId);
  // console.log("Profile Spec:", profileSpec);
  // console.log("Dat:", dat);

  // Retrieve the URL value
  var text = document.getElementById(inputId).value;
  var qrcodeContainer = document.getElementById(`qrcode${profileSpec}${dat}`);
  
  if (!qrcodeContainer) {
    console.error(`Element with ID qrcode${profileSpec}${dat} not found`);
    return; // Exit if the element is not found
  }

  qrcodeContainer.innerHTML = ""; // Clear any previous QR code

  // Generate a new QR code
  var qrcode = new QRCode(qrcodeContainer, {
    text: text,
    width: 450,
    height: 450,
    colorDark: "#000000",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.H
  });

  // Convert the QR code into a base64 image after generation
  setTimeout(function () {
    var qrCanvas = qrcodeContainer.querySelector('canvas');
    
    if (!qrCanvas) {
      console.error('Canvas not found for QR code');
      return; // Exit if canvas is not found
    }

    var qrDataURL = qrCanvas.toDataURL('image/png');

    // Send the QR code image to the server
    $.ajax({
      url: 'QRCode.php',
      type: 'POST',
      data: {
        qr_code: qrDataURL
      },
      dataType: 'json',
      success: function (response) {
        if (response.final_image) {
          // Set the final image in the display area
          var finalImageElement = document.getElementById(`final-image-display${profileSpec}${dat}`);
          finalImageElement.src = response.final_image; // Update the src with the final image URL
          finalImageElement.style.display = "block"; // Ensure the final image is visible
          
          // Optional: Hide the QR code after final image is displayed
          qrcodeContainer.style.display = "none"; // Hide the QR code if necessary
          
          // Remove the disabled attribute from the share button after the final image is ready
          var shareButton = document.querySelector(`.btn[data-clipboard-target="#final-image-display${profileSpec}${dat}"]`);
          if (shareButton) {
              shareButton.removeAttribute('disabled'); // Remove the disabled attribute
          }
        } else {
          console.error("Final image not returned from server");
        }
        console.log('QR code and final image received from server');
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error('Error saving QR code on the server:', textStatus, errorThrown);
      }
    });
  }, 500);

  // Open the Fancybox modal to display the QR code and final image
  $.fancybox.open({
    src: `#qrcodeModal${profileSpec}${dat}`,
    type: 'inline',
    opts: {
      afterShow: function (instance, current) {
        var fancyboxContainer = instance.$refs.container[0];
        fancyboxContainer.style.zIndex = '999999'; // Set higher z-index for the modal
    
        // Get the share button
        var shareButton = document.querySelector(`.btn[data-clipboard-target="#final-image-display${profileSpec}${dat}"]`);
    
        // Get the hidden input URL
        var urlToShare = document.getElementById('urlcopy' + profileSpec + dat).value;

        // console.log('URL to share:', urlToShare);
    
        // Get the QR code image element
        var qrImage = document.getElementById('final-image-display' + profileSpec + dat);
        
        // Check if the share button exists
        if (shareButton) {
            shareButton.addEventListener('click', function() {
                // Create a canvas to get the QR code image as a blob
                var canvas = document.createElement('canvas');
                var context = canvas.getContext('2d');
                canvas.width = qrImage.naturalWidth;  // Set canvas width
                canvas.height = qrImage.naturalHeight; // Set canvas height
                context.drawImage(qrImage, 0, 0);
    
                // Convert the canvas image to a Blob
                canvas.toBlob(function(blob) {
                    if (navigator.share && blob) {
                        var file = new File([blob], 'Rey Code.png', { type: 'image/png' });
    
                        // Share the QR code image and URL
                        navigator.share({
                            title: 'LLYNE Line Direct Link',
                            text: urlToShare, // Use the URL from the hidden input
                            files: [file] // Share the generated QR code image
                        }).then(() => {
                            console.log('Sharing was successful!');
                        }).catch(console.error);
                    } else {
                        console.error('Web Share API not supported or blob is null');
                    }
                }, 'image/png');
            });
        } else {
            console.error('Share button not found');
        }
    
        // Initialize Bootstrap popovers if needed
        $(function () {
            $('[data-toggle="popover"]').popover();
            $('.popover-dismiss').popover();
            $('.example-popover').popover({ container: 'body' });
        });
    }
    
    
    }
  });
}

// $('[data-fancybox].slides').fancybox({
//   // closeExisting: false,
//     buttons: false,
//     buttons: [
//       "close"
//     ],
//     infobar: true,
//     image: {
//         preload: false
//     },
//     closeExisting: false,
//     // hideScrollbar: true,
// });

$('[data-fancybox].profile-pic').fancybox({
  buttons: false,
  buttons: [
    "close"
  ],
  baseClass: "profile-view",
  closeExisting: false,
  // hideScrollbar: true,
  infobar: false,
  arrows: false,
  smallBtn: false
});

$(document).ready(function() {

  $("#profileurlrequest").modal('show');

  $('footer>div>a#home').addClass('active');


  $('footer>div>a.nav').click(function() {

      $('footer>div>a.nav').removeClass('active');

      var file = "../load/" + this.id + ".php";

      var home = $('#home');
      
      var hash = this.id;
      if(location.hash){
        history.replaceState('', document.title, window.location.pathname);
      }
      
      window.location.hash = hash;
      window.onhashchange = function () {
        if(!this.location.hash){
          $('#target-area').html('');
          home.css('display', 'block');
          $('footer>div>a.nav').removeClass('active');
          $('footer>div>a.nav#home').addClass('active');
        }
      }

      
      if (this.id != 'home') {
        $('#target-area').load(file);
        home.css('display', 'none');
      } else {
        $('#target-area').html('');
        home.css('display', 'block');
      }

      var theid = "footer>div>a" + "#" + this.id;
      $(theid).addClass('active');
    // send to home on pressing back button

  });

  if(window.location.hash){
    if(window.location.hash == '#home'){
      history.replaceState('', document.title, window.location.pathname);
    }else if(window.location.hash == '#chats' || window.location.hash == '#posts'){
      $('footer>div>a' + window.location.hash).click();
      
    }else{
      history.replaceState('', document.title, window.location.pathname);
    }
  };


  $('#llyne_connect').on('shown.bs.modal', function (e) {
    var oldHash = window.location.hash;
    var modal = this;
    var hash = modal.id;
    var home = $('#home');
    window.location.hash = hash;


    window.onhashchange = function () {
      if(!location.hash){
        
        $('.modal').modal('hide');

        if(oldHash != '#home'){
          
          window.location.hash = oldHash;
          window.onhashchange = function () {
            if(!location.hash){
              $('#target-area').html('');
              home.css('display', 'block');
              $('footer>div>a.nav').removeClass('active');
              $('footer>div>a.nav#home').addClass('active');
              history.replaceState('', document.title, window.location.pathname);
            }
          }
        }
      }
    }
  });

  $('#llyne_connect').on('hidden.bs.modal', function (e) {
    // history.replaceState('', document.title, window.location.pathname);
    // if(location.hash){
    //     window.history.back();
    //   }
    // var hash = '#home';
    // window.location.hash = hash;
    // window.onhashchange = function () {
    //   if(!this.location.hash){
    //     $('#target-area').html('');
    //     $('#home').css('display', 'block');
    //     $('footer>div>a.nav').removeClass('active');
    //     $('footer>div>a.nav#home').addClass('active');
    //     $('.modal').modal('hide');
    //   }
    // }
  });

  $('div#llyne_connect button.close').on('click', function(){
    window.history.back();
  });


  

  
var tz = jstz.determine();
var expires = "";
document.cookie = escape('timezone') + "=" + escape(tz.name()) + expires + "; path=/";


});



window.onbeforeunload = function () {
  return "Are you Sure You want to Leave this app? You may lose unsaved data";
}

// Handler for the read more functionality

$(document).on("click", ".read_more", function () {
  let target = $(this).attr('target');
  $('pre#' + target).css('display','inline');
  $('i.' + target).css('display','inline');
  $(this).css('display','none');
});



$.fancybox.defaults.hash = false; 


// var oldHash = window.location.hash;
    
    
    // $('[data-fancybox].slides').fancybox({
    //   // closeExisting: false,
    //     buttons: false,
    //     buttons: [
    //       "close"
    //     ],
    //     infobar: true,
    //     image: {
    //         preload: false
    //     },
    //     closeExisting: false,
    //     // hideScrollbar: true,
    // });
    
    // $.fancybox.open('<div class="message"><h2>Hello!</h2><p>You are awesome!</p></div>');

    //  To close all existing modals after closing fancybox to avoid modal backdrops on closing chat, etc.
    $(document).on('beforeClose.fb', function( e, instance, slide ) {
      $('.modal').modal('hide');
    });
    
    $('[data-fancybox].quickChat').fancybox({
      protect: true,
        ajax: {
            // Object containing settings for ajax request
            settings: {
              // This helps to indicate that request comes from the modal
              // Feel free to change naming
              data: {
                fancybox: true
              }
            }
          },
          closeExisting: false,
        //   hideScrollbar: true,
          buttons: false,
          infobar: false,
          baseClass: "wide-open",
          arrows: false,
          smallBtn: false,
          touch: {
            vertical: false, // Disallow to drag content vertically
          },

          afterShow: function( instance, current ) {
            history.replaceState('', document.title, window.location.pathname);
          },
      
          errorTpl: '<div class="swipe-error"><p>{{ERROR}}</p></div>',
          
        lang: "en",
        i18n: {
          en: {
            CLOSE: "Close",
            NEXT: "Next",
            PREV: "Previous",
            ERROR: "Something Went Wrong; Please check your connection. <br/> Please try again later.",
            PLAY_START: "Start slideshow",
            PLAY_STOP: "Pause slideshow",
            FULL_SCREEN: "Full screen",
            THUMBS: "Thumbnails",
            DOWNLOAD: "Download",
            SHARE: "Share",
            ZOOM: "Zoom"
          }
        },
    
        autofocus: false,
        
        // aftershow: function() {
        //     $(".chat-box").activate()
        // }
    });
    

  document.addEventListener("DOMContentLoaded", function() {
    const options = { defaultProtocol: "https" };
    document.querySelectorAll('.caption-content').forEach(function(element) {
        element.innerHTML = linkifyHtml(element.innerHTML, options);
    });
  });