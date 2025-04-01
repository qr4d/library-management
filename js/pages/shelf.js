const ShelfPage = {
    init() {
        this.loadShelfList();
        this.bindEvents();
    },

    bindEvents() {
        $('#addShelfBtn').click(() => this.handleAddShelf());
    },

    loadShelfList() {
        // 从统一响应格式中获取书架数据
        $.get('api/shelf.php', response => {
            const shelves = response.data || [];
            let html = '';
            
            shelves.forEach(shelf => {
                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">${shelf.name}</h5>
                            <p class="card-text">包含 ${shelf.book_count} 本书</p>
                            <button class="btn btn-sm btn-danger delete-shelf" 
                                    data-id="${shelf.id}">删除书架</button>
                        </div>
                    </div>
                `;
            });
            
            $('#shelfList').html(html || '<div class="alert alert-info">暂无书架</div>');
            this.bindShelfOperations();
        }).fail(() => {
            Toast.show('加载书架失败', 'danger');
        });
    },

    handleAddShelf() {
        const name = $('#newShelfInput').val().trim();
        if(!name) {
            Toast.show('书架名称不能为空', 'danger');
            return;
        }

        $('#addShelfBtn').prop('disabled', true);

        $.post('api/shelf.php', {name: name}, response => {
            if(response.success) {
                Toast.show('书架创建成功');
                $('#newShelfInput').val('');
                this.loadShelfList();
            } else {
                Toast.show(response.error || '创建失败', 'danger');
            }
        }).fail(() => {
            Toast.show('创建失败，请稍后重试', 'danger');
        }).always(() => {
            $('#addShelfBtn').prop('disabled', false);
        });
    },

    bindShelfOperations() {
        $('.delete-shelf').click(function() {
            const shelfId = $(this).data('id');
            const shelfName = $(this).closest('.card-body').find('.card-title').text();
            const bookCount = parseInt($(this).closest('.card-body').find('.card-text').text().match(/\d+/)[0]);
            
            if(bookCount > 0) {
                // 如果书架不为空,显示选择对话框
                $.get('api/shelf.php', response => {
                    const shelves = response.data || [];
                    let otherShelves = shelves.filter(s => s.id != shelfId);
                    let options = otherShelves.map(s => 
                        `<option value="${s.id}">${s.name}</option>`
                    ).join('');
                    
                    Modal.show({
                        title: '删除书架',
                        content: `
                            <p>书架"${shelfName}"包含 ${bookCount} 本书,请选择如何处理:</p>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="bookAction" 
                                       id="moveBooks" value="move" checked>
                                <label class="form-check-label" for="moveBooks">
                                    转移到其他书架
                                </label>
                                <select class="form-control mt-2" id="targetShelf">
                                    ${options}
                                </select>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bookAction" 
                                       id="deleteBooks" value="delete">
                                <label class="form-check-label" for="deleteBooks">
                                    删除所有书籍
                                </label>
                            </div>
                        `,
                        onConfirm: () => {
                            const action = $('input[name="bookAction"]:checked').val();
                            const targetShelfId = $('#targetShelf').val();
                            
                            $.ajax({
                                url: 'api/shelf.php',
                                type: 'DELETE',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    id: shelfId,
                                    action: action,
                                    target_shelf_id: targetShelfId
                                }),
                                success: response => {
                                    if(response.success) {
                                        Toast.show('书架删除成功');
                                        ShelfPage.loadShelfList();
                                    } else {
                                        Toast.show('删除失败: ' + response.error, 'danger');
                                    }
                                },
                                error: () => {
                                    Toast.show('删除失败，请稍后重试', 'danger');
                                }
                            });
                        }
                    });
                });
            } else {
                // 空书架直接确认删除
                Modal.show({
                    title: '确认删除',
                    content: `确定要删除书架"${shelfName}"吗？`,
                    onConfirm: () => {
                        $.ajax({
                            url: 'api/shelf.php',
                            type: 'DELETE',
                            contentType: 'application/json',
                            data: JSON.stringify({
                                id: shelfId,
                                action: 'delete'
                            }),
                            success: response => {
                                if(response.success) {
                                    Toast.show('书架删除成功');
                                    ShelfPage.loadShelfList();
                                } else {
                                    Toast.show('删除失败: ' + response.error, 'danger');
                                }
                            },
                            error: () => {
                                Toast.show('删除失败，请稍后重试', 'danger');
                            }
                        });
                    }
                });
            }
        });
    }
};