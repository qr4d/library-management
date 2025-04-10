const BooksPage = {
    init() {
        this.currentPage = 1;
        this.loadFilters();
        this.loadBooksList();
        this.bindEvents();
    },

    loadFilters() {
        // 加载书架选项
        $.get('api/shelf.php', response => {
            // 从统一响应格式中获取书架数据
            const shelves = response.data || [];
            
            let html = '<option value="">所有书架</option>';
            shelves.forEach(shelf => {
                html += `<option value="${shelf.id}">${shelf.name}</option>`;
            });
            $('#shelfFilter').html(html);
        });

        // 添加标记筛选
        $('#markFilter').html(`
            <option value="">所有图书</option>
            <option value="like">我喜欢的</option>
            <option value="dislike">我不喜欢的</option>
            <option value="favorite">我想看的</option>
        `);
    },

    loadBooksList() {
        const shelfId = $('#shelfFilter').val();
        const keyword = $('#searchFilter').val().trim();
        const pageSize = parseInt($('#pageSizeFilter').val()) || 12;  // 修改这行，默认为12
        const markType = $('#markFilter').val();
        
        return new Promise((resolve, reject) => {
            $.get(`api/book.php?shelf_id=${shelfId}&keyword=${encodeURIComponent(keyword)}&page=${this.currentPage}&page_size=${pageSize}&mark_type=${markType}`)
                .done(response => {
                    if (!response.success) {
                        Toast.show(response.message || '加载失败', 'danger');
                        reject(new Error(response.message));
                        return;
                    }

                    const data = response.data || {};  // 确保有数据对象
                    const books = data.data || [];     // 获取图书列表
                    const total = data.total || 0;     // 获取总数

                    if (books.length === 0) {
                        $('#booksList').html('<div class="col-12 text-center">暂无图书</div>');
                        $('#pagination').empty();
                    } else {
                        let html = '';
                        books.forEach(book => {
                            const marks = book.marks ? book.marks.split(',') : [];
                            const markButtons = `
                                <div class="btn-group btn-group-sm mt-2">
                                    <button class="btn ${marks.includes('like') ? 'btn-success' : 'btn-outline-success'} mark-book" 
                                            data-isbn="${book.isbn}" data-type="like">
                                        <i class="bi bi-hand-thumbs-up"></i> 赞 
                                        <span class="badge bg-${marks.includes('like') ? 'light text-success' : 'success'}">${book.like_count || 0}</span>
                                    </button>
                                    <button class="btn ${marks.includes('dislike') ? 'btn-danger' : 'btn-outline-danger'} mark-book" 
                                            data-isbn="${book.isbn}" data-type="dislike">
                                        <i class="bi bi-hand-thumbs-down"></i> 踩 
                                        <span class="badge bg-${marks.includes('dislike') ? 'light text-danger' : 'danger'}">${book.dislike_count || 0}</span>
                                    </button>
                                    <button class="btn ${marks.includes('favorite') ? 'btn-warning' : 'btn-outline-warning'} mark-book" 
                                            data-isbn="${book.isbn}" data-type="favorite">
                                        <i class="bi bi-star"></i> 想看
                                        <span class="badge bg-${marks.includes('favorite') ? 'light text-warning' : 'warning'}">${book.favorite_count || 0}</span>
                                    </button>
                                </div>
                            `;

                            html += `
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card">
                                        <div class="row g-0">
                                            <div class="col-12 m-2">
                                                <h5 class="card-title text-truncate" title="${book.title}">${book.title}</h5>
                                                <p class="card-text mb-1"> 作者：${book.author || '-'}</p>
                                            </div>
                                            <div class="col-4">
                                                <img src="${book.image_url || '/images/no-cover.jpg'}" 
                                                     class="img-fluid img-thumbnail"
                                                     alt="${book.title}"
                                                     onerror="this.src='/images/no-cover.jpg'"
                                                     style="object-fit: contain;">
                                            </div>
                                            <div class="col-8 ps-2">
                                                <div>
                                                    
                                                    <p class="card-text mb-1 small">${book.publisher || '-'}</p>
                                                    <p class="card-text mb-1 small">${book.pubdate || '-'}</p>
                                                    <p class="card-text mb-1 small">
                                                        分类: 
                                                        <span class="category-text">${book.category || '-'}</span>
                                                        <input type="text" class="form-control form-control-sm category-input d-none" 
                                                               value="${book.category || ''}">
                                                    </p>
                                                    <p class="card-text mb-1 small">ISBN: ${book.isbn || '-'}</p>
                                                    <p class="card-text mb-2 small">
                                                        书架: <span class="shelf-text">${book.shelf_name || '-'}</span>
                                                    </p>
                                                    <div class="btn-group admin-btn-group" role="group">
                                                        <button class="btn btn-sm btn-info edit-category" 
                                                                data-id="${book.id}">分类</button>
                                                        <button class="btn btn-sm btn-primary edit-shelf" 
                                                                data-id="${book.id}">书架</button>
                                                        <button class="btn btn-sm btn-danger delete-book" 
                                                                data-id="${book.id}">删除</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 text-center">
                                                ${markButtons}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        $('#booksList').html(html);
                        this.bindBookOperations();

                        // 渲染分页，传入正确的每页数量
                        this.renderPagination(total, pageSize);
                    }
                    resolve();
                })
                .fail(error => {
                    Toast.show('加载失败，请稍后重试', 'danger');
                    reject(error);
                });
        });
    },

    renderPagination(total, pageSize) {
        const totalPages = Math.ceil(total / pageSize);
        let html = '<nav><ul class="pagination">';
        
        // 上一页
        html += `
            <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${this.currentPage - 1}">上一页</a>
            </li>
        `;

        // 页码
        for (let i = 1; i <= totalPages; i++) {
            if (
                i === 1 || 
                i === totalPages || 
                (i >= this.currentPage - 2 && i <= this.currentPage + 2)
            ) {
                html += `
                    <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            } else if (
                i === this.currentPage - 3 || 
                i === this.currentPage + 3
            ) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // 下一页
        html += `
            <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${this.currentPage + 1}">下一页</a>
            </li>
        `;

        html += '</ul></nav>';
        $('#pagination').html(html);

        // 绑定分页事件
        $('.pagination .page-link').click(e => {
            e.preventDefault();
            const page = $(e.target).data('page');
            if (page && page !== this.currentPage) {
                this.currentPage = page;
                this.loadBooksList();
            }
        });
    },

    bindEvents() {
        // 筛选按钮
        $('#filterBtn').click(() => {
            // 显示筛选中状态
            const $btn = $('#filterBtn');
            const originalText = $btn.text();
            $btn.prop('disabled', true)
               .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 筛选中...');

            this.loadBooksList()
                .finally(() => {
                    // 恢复按钮状态
                    $btn.prop('disabled', false).text(originalText);
                });
        });
        
        // 重置按钮
        $('#resetBtn').click(() => {
            $('#shelfFilter').val('');
            $('#searchFilter').val('');
            $('#markFilter').val('');
            this.loadBooksList();
        });

        // 搜索框回车
        $('#searchFilter').on('keypress', e => {
            if(e.which === 13) {
                this.loadBooksList();
            }
        });

        // 添加每页显示数量变化事件
        $('#pageSizeFilter').change(() => {
            this.currentPage = 1; // 切换每页数量时重置到第一页
            this.loadBooksList();
        });
    },

    bindBookOperations() {
        // 修改判断逻辑,将管理员操作和标记操作分开处理
        if(!window.isAdmin) {
            // 非管理员隐藏管理操作按钮
            //$('.edit-category, .edit-shelf, .delete-book').remove();
            $('.admin-btn-group').remove();
        } else {
            // 管理员绑定管理操作事件
            this.bindAdminOperations();
        }

        // 标记按钮事件绑定移到这里,确保所有用户都能使用标记功能
        $('.mark-book').click(function() {
            if(!window.isLoggedIn) {
                Toast.show('请先登录', 'warning');
                return;
            }

            const $btn = $(this);
            const type = $btn.data('type');
            const isbn = $btn.data('isbn');
            // 根据按钮当前样式判断是否已标记
            const isMarked = $btn.hasClass(`btn-${type === 'like' ? 'success' : type === 'dislike' ? 'danger' : 'warning'}`);

            // 显示加载状态
            $btn.prop('disabled', true);

            $.ajax({
                url: 'api/book.php?action=mark',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    isbn: isbn,
                    type: type,
                    remove: isMarked
                }),
                success: response => {
                    if(response.success) {
                        const baseClass = type === 'like' ? 'success' : 
                                        type === 'dislike' ? 'danger' : 'warning';
                        
                        if(isMarked) {
                            $btn.removeClass(`btn-${baseClass}`).addClass(`btn-outline-${baseClass}`);
                            // 计数减1
                            let $count = $btn.find('.badge');
                            $count.text(Math.max(0, parseInt($count.text()) - 1));
                            $count.removeClass(`bg-light text-${baseClass}`).addClass(`bg-${baseClass}`);
                            Toast.show(`取消${type === 'like' ? ' 赞 ' : type === 'dislike' ? ' 踩 ' : ' 想看 '}`);
                        } else {
                            $btn.removeClass(`btn-outline-${baseClass}`).addClass(`btn-${baseClass}`);
                            // 计数加1
                            let $count = $btn.find('.badge');
                            $count.text(parseInt($count.text()) + 1);
                            $count.removeClass(`bg-${baseClass}`).addClass(`bg-light text-${baseClass}`);
                            Toast.show(`标记为${type === 'like' ? ' 赞 ' : type === 'dislike' ? ' 踩 ' : ' 想看 '}`);
                        }
                    } else {
                        Toast.show(response.message || '操作失败', 'danger');
                    }
                },
                error: () => {
                    Toast.show('操作失败，请稍后重试', 'danger');
                },
                complete: () => {
                    $btn.prop('disabled', false);
                }
            });
        });
    },

    // 将管理员操作相关的事件绑定移到单独的方法中
    bindAdminOperations() {
        // 编辑分类
        $('.edit-category').click(function() {
            const card = $(this).closest('.card-body');
            const categoryText = card.find('.category-text');
            const categoryInput = card.find('.category-input');
            
            if(categoryInput.hasClass('d-none')) {
                // 显示输入框
                categoryText.addClass('d-none');
                categoryInput.removeClass('d-none').focus();
                $(this).text('保存');
            } else {
                // 保存分类
                const bookId = $(this).data('id');
                const category = categoryInput.val().trim();
                
                $.ajax({
                    url: 'api/book.php',
                    type: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        id: bookId,
                        category: category
                    }),
                    success: response => {
                        if(response.success) {
                            categoryText.text(category || '-').removeClass('d-none');
                            categoryInput.addClass('d-none');
                            $(this).text('分类');
                            Toast.show('分类更新成功');
                        } else {
                            Toast.show('更新失败: ' + response.error, 'danger');
                        }
                    },
                    error: () => {
                        Toast.show('更新失败，请稍后重试', 'danger');
                    }
                });
            }
        });

        // 删除图书
        $('.delete-book').click(function() {
            const bookId = $(this).data('id');
            Modal.show({
                title: '确认删除',
                content: '确定要删除这本书吗？此操作不可恢复。',
                onConfirm: () => {
                    $.ajax({
                        url: 'api/book.php',
                        type: 'DELETE',
                        contentType: 'application/json',
                        data: JSON.stringify({ id: bookId }),
                        success: response => {
                            if(response.success) {
                                Toast.show('删除成功');
                                BooksPage.loadBooksList();
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

        // 添加编辑书架功能
        $('.edit-shelf').click(function() {
            const bookId = $(this).data('id');
            const currentShelf = $(this).closest('.card-body').find('.shelf-text').text();
            
            // 获取书架列表
            $.get('api/shelf.php', shelves => {
                let options = shelves.map(shelf => 
                    `<option value="${shelf.id}" ${shelf.name === currentShelf ? 'selected' : ''}>
                        ${shelf.name}
                    </option>`
                ).join('');
                
                Modal.show({
                    title: '选择书架',
                    content: `
                        <div class="form-group">
                            <label>选择新的书架:</label>
                            <select class="form-control" id="newShelfSelect">
                                ${options}
                            </select>
                        </div>
                    `,
                    onConfirm: () => {
                        const newShelfId = $('#newShelfSelect').val();
                        if(!newShelfId) {
                            Toast.show('请选择书架', 'danger');
                            return;
                        }
                        
                        $.ajax({
                            url: 'api/book.php',
                            type: 'PUT',
                            contentType: 'application/json',
                            data: JSON.stringify({
                                id: bookId,
                                shelf_id: newShelfId
                            }),
                            success: response => {
                                if(response.success) {
                                    Toast.show('书架更新成功');
                                    BooksPage.loadBooksList();
                                } else {
                                    Toast.show('更新失败: ' + response.error, 'danger');
                                }
                            },
                            error: () => {
                                Toast.show('更新失败，请稍后重试', 'danger');
                            }
                        });
                    }
                });
            });
        });
    }
};

