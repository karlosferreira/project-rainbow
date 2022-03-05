$Behavior.initMarketplaceEmailInviteWithTokenField = function() {
    setTimeout(function() {
        let _this = $('#invite_people_via_email');
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
    $Behavior.initMarketplaceEmailInviteWithTokenField = null;
}