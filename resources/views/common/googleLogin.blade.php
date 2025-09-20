<script src="https://accounts.google.com/gsi/client" async defer></script>

<div id="g_id_onload"
     data-client_id="{{ env('GOOGLE_CLIENT_ID') }}"
     data-callback="handleCredentialResponse"
     data-auto_prompt="false">
</div>

<button onclick="googleLogin()" class="btn btn-primary">Sign in with Google</button>

<script>
  function googleLogin() {
      window.open("{{ url('/auth/google') }}", "Google Sign-In", "width=500,height=600");
  }
</script>