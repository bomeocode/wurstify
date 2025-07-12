<script type="application/json" id="user-card-data">
  <?= json_encode($data) ?>
</script>


<div class="text-center" x-data="JSON.parse(document.getElementById('user-card-data').textContent)">

  <img :src="user.avatar ? '/uploads/avatars/' + user.avatar : '/assets/img/avatar-placeholder.png'"
    :alt="'Avatar von ' + user.username"
    class="rounded-circle mb-3"
    style="width: 120px; height: 120px; object-fit: cover;">

  <h2 class="h4" x-text="user.username"></h2>

  <template x-if="level">
    <div>
      <p class="mb-1"><span class="badge bg-primary" x-text="level.name"></span></p>
      <p class="text-muted small">
        Hat insgesamt <span x-text="ratingCount"></span> Bewertungen abgegeben.
      </p>
    </div>
  </template>

  <template x-if="user.bio">
    <p class="text-muted fst-italic mt-3" x-text="'&quot;' + user.bio + '&quot;'"></p>
  </template>
</div>