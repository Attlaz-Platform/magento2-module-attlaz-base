/// <reference path="../../../../node_modules/@types/jquery/index.d.ts" />
'use strict';
require(["jquery"], function ($) {
    $(document).ready(function () {
        AttlazBase.updateRequests();
    });
});
var AttlazBase = /** @class */ (function () {
    function AttlazBase() {
    }
    AttlazBase.log = function (msg) {
        console.log('Attlaz: ', msg);
    };
    AttlazBase.updateRequests = function () {
        var requests = [];
        jQuery('[data-update-realtime]').each(function (i, elem) {
            var requestBlock = jQuery(elem);
            requests.push(requestBlock);
        });
        this.log('Update Requests (' + requests.length + ')');
        this.update(requests);
    };
    AttlazBase.getNextRequestId = function () {
        var requestId = 'rq_' + this.currentRequestId;
        this.currentRequestId += 1;
        return requestId;
    };
    AttlazBase.update = function (blocks) {
        var _this = this;
        if (blocks.length === 0) {
            this.log('Nothing to update');
            return;
        }
        var requestsData = {};
        for (var _i = 0, blocks_1 = blocks; _i < blocks_1.length; _i++) {
            var block = blocks_1[_i];
            var data = block.data('update-realtime');
            if (data) {
                var requestId = block.data('_req_id');
                if (!requestId) {
                    requestId = this.getNextRequestId();
                    block.data('_req_id', requestId);
                }
                block.addClass(requestId);
                requestsData[requestId] = data;
                this.updateBlock(requestId, Status.State.Loading);
            }
            else {
                console.log('No data provided');
            }
            //TODO: test if data is provided
        }
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: this.requestUrl,
            context: document.body,
            data: { 'requests': requestsData }
        }).done(function (response) {
            for (var requestId in requestsData) {
                if (requestsData.hasOwnProperty(requestId)) {
                    if (response.success === true) {
                        var requestResponse = response.data[requestId];
                        if (requestResponse) {
                            try {
                                var result = requestResponse.result;
                                _this.updateBlock(requestId, Status.State.Ready, result);
                            }
                            catch (ex) {
                                _this.log('Error while updating from result (' + ex.message + ')');
                            }
                        }
                        else {
                            //debugger;
                            _this.updateBlock(requestId, Status.State.Error, '', 'No response for request');
                            _this.log('No result found for `' + requestId + '`');
                        }
                    }
                    else {
                        _this.log('Response without success (' + response.data + ')');
                        _this.updateBlock(requestId, Status.State.Error, '', response.data);
                    }
                }
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            _this.log('Error: ' + textStatus + ' (' + jqXHR.responseText + ')');
            for (var requestId in requestsData) {
                if (requestsData.hasOwnProperty(requestId)) {
                    _this.updateBlock(requestId, Status.State.Error, '', jqXHR.responseText);
                }
            }
        });
    };
    AttlazBase.initializeBlock = function (block) {
        var _this = this;
        block.off('click').click(function () {
            var requests = [];
            requests.push(block);
            _this.update(requests);
        });
        block.data('block_update_initialized', true);
    };
    AttlazBase.updateBlock = function (blockClass, status, content, extra) {
        var _this = this;
        if (content === void 0) { content = ''; }
        if (extra === void 0) { extra = ''; }
        jQuery('.' + blockClass).each(function (i, elem) {
            var block = jQuery(elem);
            var block_initialized = block.data('block_update_initialized');
            if (!block_initialized) {
                if (content === '' || content === null) {
                    content = block.html();
                }
                block.html('');
                _this.initializeBlock(block);
            }
            if (content === '' || content === null) {
                content = block.html();
                if (content.length === 0) {
                    content = '<div></div>';
                }
            }
            var contentClass = '';
            switch (status) {
                case Status.State.Loading:
                    contentClass = 'loading';
                    break;
                case Status.State.Error:
                    contentClass = 'error';
                    break;
                case Status.State.Ready:
                    contentClass = 'ready';
                    break;
            }
            var contentObj = jQuery(content);
            contentObj.addClass('request-content');
            block.removeClass('loading error ready').addClass(contentClass).html(contentObj);
        });
    };
    AttlazBase.currentRequestId = 0;
    AttlazBase.requestUrl = '/attlaz/realtime/productinfo';
    return AttlazBase;
}());
var Status;
(function (Status) {
    var State;
    (function (State) {
        State[State["Idle"] = 0] = "Idle";
        State[State["Loading"] = 1] = "Loading";
        State[State["Ready"] = 2] = "Ready";
        State[State["Error"] = 3] = "Error";
    })(State = Status.State || (Status.State = {}));
})(Status || (Status = {}));
