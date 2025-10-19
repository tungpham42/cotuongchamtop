<script>
document.addEventListener('DOMContentLoaded', () => {
  const nuocCoVolume = document.getElementById('nuoc-co');
  const hetTranVolume = document.getElementById('het-tran');
  const volumeSwitch = $('#volumeSwitch');

  // Default to unmuted if no stored state
  const savedState = localStorage.getItem('volumeState') || 'unmuted';
  localStorage.setItem('volumeState', savedState);

  const applyVolumeState = (state) => {
    const isMuted = state === 'muted';
    nuocCoVolume.muted = isMuted;
    hetTranVolume.muted = isMuted;

    if (isMuted) {
      volumeSwitch.find('i').removeClass('fa-volume-up').addClass('fa-volume-slash');
      volumeSwitch.removeClass('unmute').addClass('mute');
    } else {
      volumeSwitch.find('i').removeClass('fa-volume-slash').addClass('fa-volume-up');
      volumeSwitch.removeClass('mute').addClass('unmute');
    }
  };

  // Apply saved state on load
  applyVolumeState(savedState);

  // Toggle function
  window.toggleMute = () => {
    const newState = nuocCoVolume.muted ? 'unmuted' : 'muted';
    localStorage.setItem('volumeState', newState);
    applyVolumeState(newState);
  };
});
</script>
