import Modal from '@typo3/backend/modal.js';
import Severity from '@typo3/backend/severity.js';
import $ from 'jquery';

$(document).on('click', '[data-forward-action]', function(e){
    e.preventDefault();
    var title = $(this).attr('title');
    var action = $(this).data('forward-action');
    var $form = $($('#ForwardForm').html());
    $form.attr('action', action);
    $form.on('submit', function(){
        Modal.dismiss();
    });
    Modal.show(title, $form, Severity.info);
});


var resizeMessagePreview = function()
{
    var $box = $('#MessagePreview');
    var docH = $(document).height();
    var pos = $box.position();
    $box.height((docH - pos.top - 30) + 'px');
}

$(window).on('resize', resizeMessagePreview);
$(resizeMessagePreview);