const Toast = {
    init() {
        // 添加 toast 容器到 body
        $('body').append(`
            <div class="toast-container position-fixed bottom-0 end-0 p-3">
                <div id="operationToast" class="toast align-items-center border-0" 
                     role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body"></div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                                data-bs-dismiss="toast"></button>
                    </div>
                </div>
            </div>
        `);
    },

    show(message, type = 'success') {
        const toast = $('#operationToast');
        
        // 设置消息样式
        toast.removeClass('bg-success bg-danger text-white');
        toast.addClass(`bg-${type} text-white`);
        
        // 设置消息内容
        toast.find('.toast-body').text(message);
        
        // 显示 toast
        const bsToast = new bootstrap.Toast(toast, {
            delay: 5000  // 5秒后自动隐藏
        });
        bsToast.show();
    }
};