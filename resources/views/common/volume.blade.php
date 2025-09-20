<script>
const nuocCoVolume = document.getElementById('nuoc-co');
const hetTranVolume = document.getElementById('het-tran');
if (!localStorage.getItem('volumeState')) {
  localStorage.setItem('volumeState', 'muted');
}
if (localStorage.getItem('volumeState') == 'unmuted') {
  $('#volumeSwitch').find('i').removeClass('fa-volume-slash').addClass('fa-volume-up');
  $('#volumeSwitch').removeClass('mute').addClass('unmute');
  nuocCoVolume.muted = false;
  hetTranVolume.muted = false;
} else if (localStorage.getItem('volumeState') == 'muted') {
  $('#volumeSwitch').find('i').removeClass('fa-volume-up').addClass('fa-volume-slash');
  $('#volumeSwitch').removeClass('unmute').addClass('mute');
  nuocCoVolume.muted = true;
  hetTranVolume.muted = true;
}
function toggleMute() {
  nuocCoVolume.muted = !nuocCoVolume.muted;
  hetTranVolume.muted = !hetTranVolume.muted;
  if (nuocCoVolume.muted == false && hetTranVolume.muted == false) {
    localStorage.setItem('volumeState', 'unmuted');
    $('#volumeSwitch').find('i').removeClass('fa-volume-slash').addClass('fa-volume-up');
    $('#volumeSwitch').removeClass('mute').addClass('unmute');
  } else if (nuocCoVolume.muted == true && hetTranVolume.muted == true) {
    localStorage.setItem('volumeState', 'muted');
    $('#volumeSwitch').find('i').removeClass('fa-volume-up').addClass('fa-volume-slash');
    $('#volumeSwitch').removeClass('unmute').addClass('mute');
  }
}
</script>