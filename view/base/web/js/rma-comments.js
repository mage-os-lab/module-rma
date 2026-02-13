define([
    'jquery',
    'mage/translate',
    'jquery/jquery.cookie'
], function ($, $t) {
    'use strict';

    return function (config, element) {
        let rmaId = config.rmaId,
            saveUrl = config.saveUrl,
            loadListUrl = config.loadListUrl,
            lastCommentId = config.lastCommentId || 0,
            isAdmin = config.isAdmin || false,
            $container = $(element),
            $timeline = $container.find('#rma-comments-timeline'),
            $textarea = $container.find('#rma-comment-text'),
            $submitBtn = $container.find('#rma-comment-submit'),
            $visibleCheckbox = $container.find('#rma-comment-visible'),
            pollTimer = null,
            baseInterval = 10000,
            maxInterval = 60000,
            currentInterval = baseInterval,
            sending = false;

        function scrollToBottom() {
            $timeline.scrollTop($timeline[0].scrollHeight);
        }

        function renderComment(comment) {
            let $noComments = $timeline.find('.rma-no-comments');

            if ($noComments.length) {
                $noComments.remove();
            }

            let isAdminComment = comment.author_type === 'admin',
                isCustomerComment = comment.author_type === 'customer',
                style,
                html;

            if (isAdmin) {
                style = isAdminComment
                    ? 'background: #e8f0fe; margin-left: 20px;'
                    : 'background: #fff; margin-right: 20px; border: 1px solid #ddd;';
            } else {
                style = isCustomerComment
                    ? 'background: #e8f4e8; margin-left: 20px;'
                    : 'background: #fff; margin-right: 20px; border: 1px solid #ddd;';
            }

            html = '<div class="rma-comment ' + comment.author_type + '" data-comment-id="' + comment.entity_id + '" ' +
                'style="margin-bottom: 10px; padding: 8px 12px; border-radius: 6px; ' + style + '">' +
                '<div style="font-size: 11px; color: #666; margin-bottom: 4px;">' +
                '<strong>' + $('<span>').text(comment.author_name).html() + '</strong>';

            if (!isAdmin && isAdminComment) {
                html += '<span style="margin-left: 4px; font-size: 10px; color: #1979c3;">' + $t('Support') + '</span>';
            }

            html += '<span style="margin-left: 8px;">' + $('<span>').text(comment.created_at).html() + '</span>';

            if (isAdmin && comment.is_visible_to_customer === false) {
                html += '<span style="margin-left: 8px; color: #e67700; font-weight: bold;">' + $t('Internal Note') + '</span>';
            }

            html += '</div>' +
                '<div style="white-space: pre-wrap;">' + $('<span>').text(comment.comment).html() + '</div>' +
                '</div>';

            $timeline.append(html);
        }

        function pollComments() {
            $.ajax({
                url: loadListUrl,
                type: 'GET',
                dataType: 'json',
                data: {
                    rma_id: rmaId,
                    after_id: lastCommentId
                },
                success: function (response) {
                    if (response.success && response.comments && response.comments.length > 0) {
                        $.each(response.comments, function (i, comment) {
                            if (comment.entity_id > lastCommentId) {
                                renderComment(comment);
                                lastCommentId = comment.entity_id;
                            }
                        });

                        scrollToBottom();
                        currentInterval = baseInterval;
                    } else {
                        currentInterval = Math.min(currentInterval * 1.5, maxInterval);
                    }
                },
                error: function () {
                    currentInterval = Math.min(currentInterval * 2, maxInterval);
                },
                complete: function () {
                    schedulePoll();
                }
            });
        }

        function schedulePoll() {
            if (pollTimer) {
                clearTimeout(pollTimer);
            }

            pollTimer = setTimeout(pollComments, currentInterval);
        }

        function stopPoll() {
            if (pollTimer) {
                clearTimeout(pollTimer);
                pollTimer = null;
            }
        }

        function submitComment() {
            let commentText = $.trim($textarea.val());

            if (!commentText || sending) {
                return;
            }

            sending = true;
            $submitBtn.prop('disabled', true);

            let postData = {
                rma_id: rmaId,
                comment: commentText,
                form_key: isAdmin ? window.FORM_KEY : ($.cookie('form_key') || '')
            };

            if (isAdmin && $visibleCheckbox.length) {
                postData.is_visible_to_customer = $visibleCheckbox.is(':checked') ? 1 : 0;
            }

            $.ajax({
                url: saveUrl,
                type: 'POST',
                dataType: 'json',
                data: postData,
                success: function (response) {
                    if (response.success && response.comment) {
                        renderComment(response.comment);
                        lastCommentId = response.comment.entity_id;
                        $textarea.val('');
                        scrollToBottom();
                        currentInterval = baseInterval;
                    }
                },
                complete: function () {
                    sending = false;
                    $submitBtn.prop('disabled', false);
                    $textarea.focus();
                }
            });
        }

        // Visibility API: pause when tab is hidden, resume when visible
        $(document).on('visibilitychange', function () {
            if (document.hidden) {
                stopPoll();
            } else {
                currentInterval = baseInterval;
                pollComments();
            }
        });

        // Submit on button click
        $submitBtn.on('click', submitComment);

        // Submit on Ctrl+Enter
        $textarea.on('keydown', function (e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                submitComment();
            }
        });

        // Initial scroll and start polling
        scrollToBottom();
        schedulePoll();
    };
});
