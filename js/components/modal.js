const Modal = {
    show(options) {
        const { title, content, onConfirm, onCancel } = options;
        
        const modalHtml = `
            <div class="modal fade" id="commonModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">${content}</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                            <button type="button" class="btn btn-primary" id="modalConfirmBtn">确定</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHtml);
        const modal = new bootstrap.Modal('#commonModal');
        
        $('#modalConfirmBtn').click(() => {
            if (onConfirm) onConfirm();
            modal.hide();
        });
        
        $('#commonModal').on('hidden.bs.modal', function() {
            if (onCancel) onCancel();
            $(this).remove();
        });
        
        modal.show();
        return modal;
    }
};