const SearchPage = {
    init() {
        this.bindEvents();
    },

    bindEvents() {
        $('#searchBtn').click(() => {
            const keyword = $('#searchInput').val().trim();
            if(!keyword) {
                Toast.show('请输入搜索关键词', 'warning');
                return;
            }
            
            // 显示搜索中状态
            const $btn = $('#searchBtn');
            const originalText = $btn.text();
            $btn.prop('disabled', true)
               .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 搜索中...');

            // 同时查询豆瓣和本地数据库
            Promise.all([
                $.get(`api/search.php?q=${encodeURIComponent(keyword)}`),
                $.get(`api/book.php?keyword=${encodeURIComponent(keyword)}`)
            ]).then(([doubanResult, localBooks]) => {
                // 从统一响应格式中获取豆瓣搜索数据
                const doubanData = doubanResult.data;
                if (!doubanData || !doubanData.books) {
                    Toast.show('搜索结果格式错误', 'danger');
                    return;
                }

                // 从统一的响应格式中获取本地图书数据
                const localBooksData = localBooks.data?.data || []; // 修改这里，获取正确的数据结构
                
                // 转换本地图书为 Map，使用 ISBN 作为键
                const localBooksMap = new Map(
                    localBooksData.map(book => [book.isbn, book])
                );
                
                this.displayResults(doubanData, localBooksMap);

                // 显示搜索结果区域,隐藏其他页面内容
                $('#page-content').hide();
                $('#searchResults').show();
            }).catch((error) => {
                console.error('Search error:', error);
                Toast.show('搜索失败，请稍后重试', 'danger');
            }).finally(() => {
                // 恢复按钮状态
                $btn.prop('disabled', false).text(originalText);
            });
        });

        // 添加回车搜索支持
        $('#searchInput').keypress(function(e) {
            if(e.which == 13) {
                $('#searchBtn').click();
            }
        });
    },

    displayResults(data, localBooksMap) {
        let html = '';
        if (!data.books || data.books.length === 0) {
            $('#searchResults').html('<div class="col-12"><div class="alert alert-info">未找到相关图书</div></div>');
            return;
        }

        data.books.forEach(book => {
            const localBook = localBooksMap.get(book.isbn13);
            let actionHtml = '';
            
            if(localBook) {
                actionHtml = `<div class="alert alert-info">已在「${localBook.shelf_name}」书架</div>`;
            } else {
                actionHtml = window.isLoggedIn ? 
                    `<button class="btn btn-primary add-book" data-id="${book.id}" data-isbn="${book.isbn13}">加入书架</button>` :
                    `<a href="login.php" class="btn btn-primary">登录后加入书架</a>`;
            }

            html += `
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <img src="${book.image || '/images/no-cover.jpg'}" 
                                     class="img-fluid rounded-start h-100" 
                                     alt="${book.title}"
                                     style="object-fit: contain;">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">${book.title}</h5>
                                    <p class="card-text">作者: ${Array.isArray(book.author) ? book.author.join(', ') : book.author}</p>
                                    <p class="card-text">出版社: ${book.publisher || '未知'}</p>
                                    <p class="card-text">ISBN: ${book.isbn13}</p>
                                    ${actionHtml}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#searchResults').html(html);
        
        // 如果是新书才绑定加入书架事件
        if(window.isLoggedIn) {
            this.bindAddBookEvents();
        }
    },

    bindAddBookEvents() {
        $('.add-book').click(function() {
            const bookId = $(this).data('id');
            const bookIsbn = $(this).data('isbn');
            
            // 从统一响应格式中获取书架数据
            $.get('api/shelf.php', response => {
                const shelves = response.data || [];
                if(!shelves.length) {
                    Toast.show('请先创建书架', 'warning');
                    return;
                }

                let options = shelves.map(shelf => 
                    `<option value="${shelf.id}">${shelf.name}</option>`
                ).join('');
                
                Modal.show({
                    title: '选择书架',
                    content: `
                        <div class="form-group">
                            <label>将图书加入到:</label>
                            <select class="form-control" id="targetShelf">
                                ${options}
                            </select>
                        </div>
                    `,
                    onConfirm: () => {
                        const shelfId = $('#targetShelf').val();
                        if(!shelfId) {
                            Toast.show('请选择书架', 'danger');
                            return;
                        }
                        
                        // 获取图书详情并保存
                        $.get(`api/isbn.php?isbn=${bookIsbn}`, response => {
                            if(response.error) {
                                Toast.show(response.error, 'danger');
                                return;
                            }

                            const bookDetail = response.data;
                            bookDetail.shelf_id = shelfId;
                            console.log('Book detail:', bookDetail); // 调试输出
                            
                            $.ajax({
                                url: 'api/book.php',
                                type: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify(bookDetail),
                                success: response => {
                                    if(response.success) {
                                        Toast.show('图书添加成功');
                                        // 刷新当前搜索结果
                                        $('#searchBtn').click();
                                    } else {
                                        Toast.show(response.error || '添加失败', 'danger');
                                    }
                                },
                                error: () => {
                                    Toast.show('添加失败，请稍后重试', 'danger');
                                }
                            });
                        });
                    }
                });
            });
        });
    }
};