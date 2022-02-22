$Behavior.initGroupEmailInviteWithTokenField = function () {
    setTimeout(function() {
        let _this = $('#emails');
        if (_this.closest('.tokenfield').length) {
            _this.closest('.tokenfield').find('.token-input').on('blur', function() {
                let tokenInput = $(this),
                    currentText = tokenInput.val();
                if (typeof currentText === "string" && currentText !== '') {
                    _this.tokenfield('createToken', currentText);
                    tokenInput.val('');
                }
            });
        }
    }, 500);
    $Behavior.initGroupEmailInviteWithTokenField = null;
}