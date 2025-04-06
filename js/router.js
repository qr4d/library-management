const Router = {
    init() {
        // 注册路由
        this.routes = {
            'search': SearchPage,
            'quick-add': QuickAddPage,
            'shelf': ShelfPage,
            'all-books': BooksPage
        };
    },

    loadPage(page) {
        // 确保页面内容可见
        $('#page-content').show();
        
        const content = this.getPageTemplate(page);
        $('#page-content').html(content);
        
        // 初始化对应页面
        if(this.routes[page]) {
            this.routes[page].init();
        }
    },

    getPageTemplate(page) {
        switch(page) {
            case 'search':
                return `
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchInput" 
                                       placeholder="输入书名、作者、ISBN或内容关键词...">
                                <button class="btn btn-primary" type="button" id="searchBtn">全网搜索</button>
                            </div>
                        </div>
                    </div>
                    <div id="searchResults" class="row"></div>
                `;

            case 'quick-add':
                return `
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">快速录入</h5>
                                    <div class="form-group mb-3">
                                        <label>选择书架:</label>
                                        <select class="form-control" id="shelfSelect"></select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>ISBN:</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="isbnInput" 
                                                   placeholder="扫描或输入ISBN">
                                            <button class="btn btn-primary" id="queryIsbnBtn">查询</button>
                                            <button class="btn btn-secondary" id="clearIsbnBtn">清空</button>
                                        </div>
                                    </div>
                                    <div id="bookInfo"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

            case 'shelf':
                return `
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">新增书架</h5>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="newShelfInput" 
                                               placeholder="输入书架名称">
                                        <button class="btn btn-primary" id="addShelfBtn">添加</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div id="shelfList"></div>
                        </div>
                    </div>
                `;

            case 'all-books':
                return `
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">图书列表</h5>
                                    <div class="row mb-3">
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <select class="form-control" id="shelfFilter">
                                                <option value="">所有书架</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <input type="text" class="form-control" id="searchFilter" 
                                                   placeholder="搜索书名/作者...">
                                        </div>
                                        <div class="col-md-2 col-sm-6 mb-2">
                                            <select class="form-control" id="markFilter">
                                                <option value="">所有图书</option>
                                                <option value="like">我喜欢的</option>
                                                <option value="dislike">我不喜欢的</option>
                                                <option value="favorite">我的收藏</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-sm-6 mb-2">
                                            <select class="form-control" id="pageSizeFilter">
                                                <option value="6">6本/页</option>
                                                <option value="12">12本/页</option>
                                                <option value="30">30本/页</option>
                                                <option value="48">48本/页</option>
                                                <option value="90">90本/页</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-sm-6">
                                            <button class="btn btn-primary" id="filterBtn">筛选</button>
                                            <button class="btn btn-secondary" id="resetBtn">重置</button>
                                        </div>
                                    </div>
                                    <div id="booksList" class="row"></div>
                                    <div id="pagination" class="d-flex justify-content-center mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
        }
    }
};