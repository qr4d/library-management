$(document).ready(function() {
    // 初始化 Toast
    Toast.init();
    
    // 初始化路由
    Router.init();
    
    // 页面路由处理
    $('.nav-link').click(function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        Router.loadPage(page);
        // 清空搜索结果
        $('#searchResults').empty();
    });

    // 默认加载所有图书页面
    Router.loadPage('all-books');
});

// 添加对应的脚本引用到 index.html
// <script src="js/components/toast.js"></script>
// <script src="js/components/modal.js"></script>
// <script src="js/router.js"></script>
// <script src="js/pages/search.js"></script>
// <script src="js/pages/quickAdd.js"></script>
// <script src="js/pages/shelf.js"></script>
// <script src="js/pages/books.js"></script>