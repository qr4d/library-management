const QuickAddPage = {
    init() {
        this.loadShelves();
        this.bindEvents();
    },

    loadShelves() {
        $.get('api/shelf.php', response => {
            // 从统一响应格式中获取书架数据
            const shelves = response.data || [];
            
            let html = '<option value="">选择书架...</option>';
            shelves.forEach(shelf => {
                html += `<option value="${shelf.id}">${shelf.name}</option>`;
            });
            $('#shelfSelect').html(html);
        });
    },

    bindEvents() {
        // 添加回车键触发查询和录入
        $('#isbnInput').keypress(e => {
            if (e.which === 13) { // 回车键
                e.preventDefault();
                // 如果已经显示了录入按钮，则触发录入
                if ($('#addBookBtn').length > 0) {
                    $('#addBookBtn').click();
                } else {
                    // 否则触发查询
                    $('#queryIsbnBtn').click();
                }
            }
        });

        // ISBN查询按钮事件 
        $('#queryIsbnBtn').click(() => {
            const isbn = $('#isbnInput').val().trim();
            if(!isbn) {
                Toast.show('请输入ISBN', 'danger');
                return;
            }

            // 显示查询中状态
            const $btn = $('#queryIsbnBtn');
            const originalText = $btn.text();
            $btn.prop('disabled', true)
               .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 查询中...');
            
            // 首先查询本地数据库
            $.get(`api/book.php?keyword=${isbn}`, response => {
                const localBooks = response.data || [];
                if(localBooks.length > 0) {
                    const book = localBooks[0];
                    $('#bookInfo').html(`
                        <div class="alert alert-info mt-4">
                            《${book.title}》已在「${book.shelf_name}」书架
                        </div>
                    `);
                    // 本地查询完成后恢复按钮和清空输入框
                    $btn.prop('disabled', false).text(originalText);
                    $('#isbnInput').val('').focus(); // 添加这行来清空和聚焦输入框
                } else {
                    // 本地未找到则查询豆瓣
                    $.get(`api/isbn.php?isbn=${isbn}`, response => {
                        if(response.error) {
                            Toast.show(response.error, 'danger');
                        } else {
                            // 使用响应中的数据
                            const bookData = response.data || response;
                            this.displayBookInfo(bookData);
                        }
                        // 豆瓣查询完成后恢复按钮
                        $btn.prop('disabled', false).text(originalText);
                    }).fail(() => {
                        Toast.show('查询失败，请稍后重试', 'danger');
                        // 查询失败时恢复按钮
                        $btn.prop('disabled', false).text(originalText);
                    });
                }
            }).fail(() => {
                Toast.show('查询失败，请稍后重试', 'danger');
                // 查询失败时恢复按钮
                $btn.prop('disabled', false).text(originalText);
            });
        });

        // 清空按钮事件
        $('#clearIsbnBtn').click(() => {
            $('#isbnInput').val('').focus();
            $('#bookInfo').empty();
        });
    },

    displayBookInfo(book) {
        const html = `
            <div class="mt-4">
                <div class="row">
                    <div class="col-md-4">
                        <img src="${book.image}" class="img-fluid" alt="${book.title}">
                    </div>
                    <div class="col-md-8">
                        <h4>${book.title}</h4>
                        <p>作者: ${book.author.join(', ')}</p>
                        <p>出版社: ${book.publisher}</p>
                        <p>出版日期: ${book.pubdate}</p>
                        <button class="btn btn-primary" id="addBookBtn">录入此书</button>
                    </div>
                </div>
            </div>
        `;
        
        $('#bookInfo').html(html);
        
        // 绑定录入按钮事件
        $('#addBookBtn').click(() => {
            const shelfId = $('#shelfSelect').val();
            if(!shelfId) {
                Toast.show('请选择书架', 'danger');
                $('#shelfSelect').focus();
                return;
            }
            
            // 准备要保存的图书数据
            book.shelf_id = shelfId;
            
            // 禁用按钮防止重复提交
            $('#addBookBtn').prop('disabled', true);
            
            // 发送保存请求
            $.ajax({
                url: 'api/book.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(book),
                success: response => {
                    if(response.success) {
                        Toast.show(`《${book.title}》录入成功`);
                        // 清空并聚焦到输入框
                        $('#isbnInput').val('').focus();
                        $('#bookInfo').empty();
                    } else {
                        Toast.show(response.error || '录入失败', 'danger');
                    }
                },
                error: () => {
                    Toast.show('录入失败，请稍后重试', 'danger');
                },
                complete: () => {
                    // 无论成功失败都启用按钮
                    $('#addBookBtn').prop('disabled', false);
                }
            });
        });
    }
};

