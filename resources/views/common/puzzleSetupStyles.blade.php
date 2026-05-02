<style>
body.puzzle.setup .game > .container {
  max-width: 1180px;
}

body.puzzle.setup #ban-co {
  width: min(100%, 560px) !important;
  max-width: 560px;
}

body.puzzle.setup .game h5 {
  line-height: 1.25;
  margin-bottom: .5rem !important;
}

body.puzzle.setup .game .btn-lg {
  font-size: 1rem;
  line-height: 1.35;
  padding: .65rem .9rem;
}

body.puzzle.setup .game p.w-100.text-center,
body.puzzle.setup .game .dropup.mx-auto.text-center {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  gap: .5rem;
  margin-bottom: .5rem !important;
}

body.puzzle.setup .game p.w-100.text-center .btn,
body.puzzle.setup .game .dropup.mx-auto.text-center > .btn,
body.puzzle.setup .game .dropup.mx-auto.text-center > button {
  width: auto !important;
  min-width: 170px;
  max-width: 100%;
  margin: 0 !important;
  white-space: normal;
}

body.puzzle.setup .sharethis-inline-reaction-buttons {
  min-height: 76px;
  max-width: 460px;
  margin: 1rem auto;
}

body.puzzle.setup #copy-url {
  max-width: 640px;
  width: 100% !important;
}

@media (min-width: 992px) {
  body.puzzle.setup .game .row > .col-lg-6 {
    overflow: visible;
  }

  body.puzzle.setup .game .row > .col-lg-6:first-child {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  body.puzzle.setup .game .row > .col-lg-6:last-child {
    align-self: center;
  }
}

@media (max-width: 575.98px) {
  body.puzzle.setup .game .btn-lg {
    font-size: .875rem;
    padding: .55rem .75rem;
  }

  body.puzzle.setup .game p.w-100.text-center .btn,
  body.puzzle.setup .game .dropup.mx-auto.text-center > .btn,
  body.puzzle.setup .game .dropup.mx-auto.text-center > button {
    width: 100% !important;
    min-width: 0;
  }
}
</style>
