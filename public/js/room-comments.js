(function (window, document) {
  'use strict';

  var MAX_ATTEMPTS = 50;
  var ATTEMPT_DELAY = 120;

  function waitForjQuery(attempts) {
    if (typeof window.jQuery !== 'undefined') {
      onReady(init);
      return;
    }
    if (attempts <= 0) {
      return;
    }
    setTimeout(function () {
      waitForjQuery(attempts - 1);
    }, ATTEMPT_DELAY);
  }

  function onReady(callback) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', callback);
    } else {
      callback();
    }
  }

  function init() {
    var $ = window.jQuery;
    if (!$) {
      return;
    }

    var config = window.__ROOM_COMMENT_CONFIG__ || null;
    if (!config || !config.code || !config.endpoints) {
      return;
    }

    var endpoints = config.endpoints;
    var commentsEndpoint = endpoints.list || '';
    if (!commentsEndpoint) {
      return;
    }
    var commentStoreEndpoint = endpoints.store || commentsEndpoint;
    var commentLikeBaseEndpoint = endpoints.likeBase || commentStoreEndpoint;
    var storageKey = config.storageKey || ('room_comment_likes_' + config.code);
    var texts = config.texts || {};
    var locale = config.locale || 'en-US';
    var bootboxLocale = config.bootboxLocale || 'en';
    var csrfToken = config.csrfToken || '';

    var likeLabel = texts.likeLabel || 'Like';
    var replyLabel = texts.replyLabel || 'Reply';
    var replyPlaceholder = texts.replyPlaceholder || 'Write your reply...';
    var replySubmit = texts.replySubmit || 'Send';
    var replySending = texts.replySending || 'Sending...';
    var replyCancel = texts.replyCancel || 'Cancel';
    var anonymousLabel = texts.anonymous || 'Anonymous';
    var emptyState = texts.emptyState || 'No comments yet.';
    var successSubmit = texts.successSubmit || 'Thanks! Your comment has been posted.';
    var errorLoad = texts.errorLoad || 'Unable to load comments. Please try again later.';
    var errorSubmit = texts.errorSubmit || 'Unable to post your comment. Please try again.';
    var errorReply = texts.errorReply || 'Unable to send your reply. Please try again.';
    var errorLike = texts.errorLike || 'Unable to like this comment. Please try again.';
    var errorContentRequired = texts.errorContentRequired || 'Please enter your comment.';
    var errorReplyRequired = texts.errorReplyRequired || 'Please enter your reply.';
    var sendingLabel = texts.sendingLabel || 'Sending...';
    var submitLabel = texts.submitLabel || 'Post comment';

    var form = $('#room-comment-form');
    var authorInput = $('#room_comment_author');
    var contentInput = $('#room_comment_content');
    var feedback = $('#room-comment-feedback');
    var submitBtn = $('#room-comment-submit');
    var commentList = $('#room-comment-list');

    if (!form.length || !commentList.length) {
      return;
    }

    function formatCommentDate(dateString) {
      if (!dateString) return '';
      var date = new Date(dateString);
      if (!isNaN(date.getTime())) {
        try {
          return date.toLocaleString(locale, { hour12: false });
        } catch (error) {
          return date.toISOString();
        }
      }
      return dateString;
    }

    function loadStoredLikedComments() {
      if (typeof localStorage === 'undefined') {
        return [];
      }
      try {
        var raw = localStorage.getItem(storageKey);
        if (!raw) {
          return [];
        }
        var parsed = JSON.parse(raw);
        if (!Array.isArray(parsed)) {
          return [];
        }
        return Array.from(new Set(parsed
          .map(function (id) { return parseInt(id, 10); })
          .filter(function (id) { return !Number.isNaN(id); })));
      } catch (error) {
        return [];
      }
    }

    var likedCommentIds = loadStoredLikedComments();

    function hasLikedComment(id) {
      var numericId = Number(id);
      if (Number.isNaN(numericId)) {
        return false;
      }
      return likedCommentIds.indexOf(numericId) !== -1;
    }

    function persistLikedComments() {
      if (typeof localStorage === 'undefined') {
        return;
      }
      try {
        localStorage.setItem(storageKey, JSON.stringify(likedCommentIds));
      } catch (error) {
        // Storage might be unavailable; ignore.
      }
    }

    function rememberLikedComment(id) {
      var numericId = Number(id);
      if (Number.isNaN(numericId) || hasLikedComment(numericId)) {
        return;
      }
      likedCommentIds.push(numericId);
      likedCommentIds = Array.from(new Set(likedCommentIds));
      persistLikedComments();
    }

    function escapeAndFormat(content) {
      return $('<div>').text(content || '').html().replace(/\n/g, '<br>');
    }

    function createReplyForm(commentId) {
      var form = $('<form class="comment-reply-form d-none"></form>');
      if (commentId) {
        form.attr('data-comment-id', commentId);
      }
      form.append(
        '<div class="form-group mb-2">' +
        '<input type="text" class="form-control form-control-sm reply-author" maxlength="120" placeholder="' +
        $('<div>').text(texts.authorPlaceholder || '').html() +
        '">' +
        '</div>'
      );
      form.append(
        '<div class="form-group mb-2">' +
        '<textarea class="form-control form-control-sm reply-content" rows="2" maxlength="1000" placeholder="' +
        $('<div>').text(replyPlaceholder).html() +
        '" required></textarea>' +
        '</div>'
      );
      form.append('<small class="reply-feedback d-none"></small>');
      var actions = $('<div class="d-flex justify-content-end gap-2"></div>');
      actions.append('<button type="button" class="btn btn-outline-light btn-sm comment-reply-cancel">' + $('<div>').text(replyCancel).html() + '</button>');
      actions.append('<button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-paper-plane"></i> ' + $('<div>').text(replySubmit).html() + '</button>');
      form.append(actions);
      return form;
    }

    function buildCommentCard(comment, level) {
      if (level === void 0) level = 0;
      var author = comment.author_name && comment.author_name.trim().length ? comment.author_name : anonymousLabel;
      var createdAt = comment.created_at ? formatCommentDate(comment.created_at) : '';
      var initials = author.trim().length ? author.trim().charAt(0).toUpperCase() : anonymousLabel.charAt(0);
      var commentId = Number(comment.id);
      var likeCount = Math.max(parseInt(comment.likes_count || 0, 10) || 0, 0);

      var card = $('<div class="room-comment-card"></div>');
      if (level > 0) {
        card.addClass('reply');
      }
      if (!Number.isNaN(commentId)) {
        card.attr('data-comment-id', commentId);
      }

      var header = $('<div class="comment-header"></div>');
      header.append('<div class="comment-avatar">' + $('<div>').text(initials).html() + '</div>');
      var userInfo = $('<div></div>');
      userInfo.append('<div class="font-weight-bold">' + $('<div>').text(author).html() + '</div>');
      if (createdAt) {
        userInfo.append('<div class="small text-muted"><i class="far fa-clock"></i> ' + $('<div>').text(createdAt).html() + '</div>');
      }
      header.append(userInfo);
      card.append(header);

      card.append('<div class="comment-body">' + escapeAndFormat(comment.content || '') + '</div>');

      var actions = $('<div class="comment-actions"></div>');
      var likeAction = $('<span class="comment-action comment-like"></span>');
      if (!Number.isNaN(commentId)) {
        likeAction.attr('data-comment-id', commentId);
      }
      likeAction.append('<i class="far fa-thumbs-up"></i>');
      likeAction.append(' <span class="like-label">' + $('<div>').text(likeLabel).html() + '</span>');
      likeAction.append(' <span class="comment-like-count">' + likeCount + '</span>');
      if (hasLikedComment(commentId)) {
        likeAction.addClass('liked');
      }
      actions.append(likeAction);

      var replyAction = $('<span class="comment-action comment-reply-toggle"></span>');
      if (!Number.isNaN(commentId)) {
        replyAction.attr('data-comment-id', commentId);
      }
      replyAction.append('<i class="far fa-comment"></i>');
      replyAction.append(' ' + $('<div>').text(replyLabel).html());
      actions.append(replyAction);

      card.append(actions);

      var replyForm = createReplyForm(Number.isNaN(commentId) ? null : commentId);
      card.append(replyForm);

      if (Array.isArray(comment.replies) && comment.replies.length) {
        var childrenWrapper = $('<div class="room-comment-children"></div>');
        comment.replies.forEach(function (reply) {
          childrenWrapper.append(buildCommentCard(reply, level + 1));
        });
        card.append(childrenWrapper);
      }

      return card;
    }

    function renderComments(comments) {
      commentList.empty();
      if (!comments || !comments.length) {
        commentList.append('<div class="room-empty-comment">' + $('<div>').text(emptyState).html() + '</div>');
        return;
      }
      comments.forEach(function (comment) {
        commentList.append(buildCommentCard(comment, 0));
      });
    }

    function loadComments() {
      $.get(commentsEndpoint)
        .done(function (response) {
          var payload = (response && response.comments) ? response.comments : [];
          renderComments(payload);
        })
        .fail(function () {
          commentList.empty().append('<div class="room-empty-comment error">' + $('<div>').text(errorLoad).html() + '</div>');
        });
    }

    form.on('submit', function (event) {
      event.preventDefault();
      var author = (authorInput.val() || '').trim();
      var content = (contentInput.val() || '').trim();

      if (!content.length) {
        feedback.removeClass('d-none text-success').addClass('text-danger').text(errorContentRequired);
        return;
      }

      submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + sendingLabel);
      feedback.addClass('d-none').removeClass('text-danger text-success').text('');

      $.ajax({
        url: commentStoreEndpoint,
        method: 'POST',
        data: {
          author_name: author,
          content: content
        },
        headers: {
          'X-CSRF-TOKEN': csrfToken
        }
      }).done(function () {
        authorInput.val('');
        contentInput.val('');
        feedback.removeClass('d-none text-danger').addClass('text-success').text(successSubmit);
        loadComments();
        setTimeout(function () {
          feedback.addClass('d-none').text('').removeClass('text-success');
        }, 4000);
      }).fail(function (xhr) {
        var message = errorSubmit;
        if (xhr && xhr.responseJSON) {
          if (xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
          } else if (xhr.responseJSON.errors) {
            try {
              message = Object.values(xhr.responseJSON.errors).flat().join(' ');
            } catch (err) {
              message = errorSubmit;
            }
          }
        }
        feedback.removeClass('d-none text-success').addClass('text-danger').text(message);
      }).always(function () {
        submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> ' + submitLabel);
      });
    });

    commentList.on('click', '.comment-like', function () {
      var $btn = $(this);
      if ($btn.hasClass('loading') || $btn.hasClass('liked')) {
        return;
      }
      var commentId = Number($btn.data('comment-id'));
      if (Number.isNaN(commentId) || commentId <= 0) {
        return;
      }

      $btn.addClass('loading');
      $.ajax({
        url: commentLikeBaseEndpoint + '/' + commentId + '/like',
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken
        }
      }).done(function (response) {
        var likesCount = Math.max(parseInt((response && response.likes_count) || 0, 10) || 0, 0);
        $btn.find('.comment-like-count').text(likesCount);
        $btn.addClass('liked');
        rememberLikedComment(commentId);
      }).fail(function (xhr) {
        var message = errorLike;
        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
          message = xhr.responseJSON.message;
        }
        if (window.bootbox && typeof window.bootbox.alert === 'function') {
          window.bootbox.alert({
            message: message,
            locale: bootboxLocale,
            centerVertical: true,
            closeButton: false,
            size: 'small'
          });
        } else {
          window.alert(message);
        }
      }).always(function () {
        $btn.removeClass('loading');
      });
    });

    commentList.on('click', '.comment-reply-toggle', function () {
      var form = $(this).closest('.room-comment-card').find('.comment-reply-form').first();
      form.toggleClass('d-none');
      if (!form.hasClass('d-none')) {
        form.find('.reply-content').trigger('focus');
      }
    });

    commentList.on('click', '.comment-reply-cancel', function () {
      var replyForm = $(this).closest('.comment-reply-form');
      replyForm.addClass('d-none');
      if (replyForm.length && replyForm[0]) {
        replyForm[0].reset();
      }
      replyForm.find('.reply-feedback').addClass('d-none').text('').removeClass('text-danger text-success');
    });

    commentList.on('submit', '.comment-reply-form', function (event) {
      event.preventDefault();
      var replyForm = $(this);
      var submitButton = replyForm.find('button[type="submit"]');
      var replyFeedback = replyForm.find('.reply-feedback');
      var author = (replyForm.find('.reply-author').val() || '').trim();
      var content = (replyForm.find('.reply-content').val() || '').trim();
      var parentId = replyForm.data('comment-id');

      if (!content.length) {
        replyFeedback.removeClass('d-none text-success').addClass('text-danger').text(errorReplyRequired);
        return;
      }

      submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + replySending);
      replyFeedback.addClass('d-none').removeClass('text-danger text-success').text('');

      $.ajax({
        url: commentStoreEndpoint,
        method: 'POST',
        data: {
          author_name: author,
          content: content,
          parent_id: parentId
        },
        headers: {
          'X-CSRF-TOKEN': csrfToken
        }
      }).done(function () {
        if (replyForm.length && replyForm[0]) {
          replyForm[0].reset();
        }
        replyForm.addClass('d-none');
        loadComments();
      }).fail(function (xhr) {
        var message = errorReply;
        if (xhr && xhr.responseJSON) {
          if (xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
          } else if (xhr.responseJSON.errors) {
            try {
              message = Object.values(xhr.responseJSON.errors).flat().join(' ');
            } catch (err) {
              message = errorReply;
            }
          }
        }
        replyFeedback.removeClass('d-none text-success').addClass('text-danger').text(message);
      }).always(function () {
        submitButton.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> ' + replySubmit);
      });
    });

    loadComments();
  }

  waitForjQuery(MAX_ATTEMPTS);
})(window, document);
