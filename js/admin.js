const AdminRouter = {
    init() {
        this.bindEvents();
        // 默认加载第一个可见的页面
        const firstPage = $('.nav-link[data-page]:visible').first().data('page');
        this.loadPage(firstPage);
    },

    bindEvents() {
        $('.nav-link[data-page]').click(function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            AdminRouter.loadPage(page);
        });
    },

    loadPage(page) {
        const content = this.getTemplate(page);
        $('#page-content').html(content);
        this.initPage(page);
    },

    getTemplate(page) {
        switch(page) {
            case 'users':
                return `
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title d-flex justify-content-between">
                                用户管理
                                <button class="btn btn-primary" id="addUserBtn">添加用户</button>
                            </h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>用户名</th>
                                            <th>角色</th>
                                            <th>状态</th>
                                            <th>创建时间</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="userList"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;

            case 'logs':
                return `
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">操作日志</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>时间</th>
                                            <th>用户</th>
                                            <th>操作</th>
                                            <th>对象类型</th>
                                            <th>详情</th>
                                        </tr>
                                    </thead>
                                    <tbody id="logList"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;

            case 'books':
                return `
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">图书管理</h5>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <select class="form-control" id="shelfFilter">
                                        <option value="">所有书架</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="searchFilter" 
                                           placeholder="搜索书名/作者...">
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary" id="filterBtn">筛选</button>
                                    <button class="btn btn-secondary" id="resetBtn">重置</button>
                                </div>
                            </div>
                            <div id="booksList" class="row"></div>
                        </div>
                    </div>
                `;

            case 'shelves':
                return `
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">书架管理</h5>
                            <div class="mb-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="newShelfInput" 
                                           placeholder="输入书架名称">
                                    <button class="btn btn-primary" id="addShelfBtn">添加</button>
                                </div>
                            </div>
                            <div id="shelfList"></div>
                        </div>
                    </div>
                `;
        }
    },

    initPage(page) {
        switch(page) {
            case 'users':
                this.loadUsers();
                this.bindUserEvents();
                break;
            case 'logs':
                this.loadLogs();
                break;
            case 'books':
                BooksPage.init();
                break;
            case 'shelves':
                ShelfPage.init();
                break;
        }
    },

    loadUsers() {
        $.get('api/user.php', data => {
            let html = '';
            data.forEach(user => {
                const roleName = user.role == 2 ? '用户管理员' : 
                               user.role == 1 ? '普通管理员' : '普通用户';
                html += `
                    <tr>
                        <td>${user.username}</td>
                        <td>${roleName}</td>
                        <td>${user.status == 1 ? '启用' : '禁用'}</td>
                        <td>${user.created_at}</td>
                        <td>
                            <button class="btn btn-sm btn-info edit-user" 
                                    data-id="${user.id}">编辑</button>
                            <button class="btn btn-sm btn-warning reset-pwd" 
                                    data-id="${user.id}">重置密码</button>
                            <button class="btn btn-sm btn-${user.status == 1 ? 'danger' : 'success'} toggle-status" 
                                    data-id="${user.id}" data-status="${user.status}">
                                    ${user.status == 1 ? '禁用' : '启用'}
                            </button>
                        </td>
                    </tr>
                `;
            });
            $('#userList').html(html);
        });
    },

    loadLogs() {
        $.get('api/log.php', data => {
            let html = '';
            data.forEach(log => {
                html += `
                    <tr>
                        <td>${log.created_at}</td>
                        <td>${log.username}</td>
                        <td>${log.action}</td>
                        <td>${log.target_type}</td>
                        <td>${log.detail}</td>
                    </tr>
                `;
            });
            $('#logList').html(html);
        });
    },

    bindUserEvents() {
        // 添加用户
        $('#addUserBtn').click(() => {
            Modal.show({
                title: '添加用户',
                content: `
                    <div class="form-group mb-3">
                        <label>用户名</label>
                        <input type="text" class="form-control" id="newUsername">
                    </div>
                    <div class="form-group mb-3">
                        <label>密码</label>
                        <input type="password" class="form-control" id="newPassword">
                    </div>
                    <div class="form-group mb-3">
                        <label>角色</label>
                        <select class="form-control" id="newRole">
                            <option value="0">普通用户</option>
                            <option value="1">普通管理员</option>
                            <option value="2">用户管理员</option>
                        </select>
                    </div>
                `,
                onConfirm: () => {
                    const data = {
                        username: $('#newUsername').val(),
                        password: $('#newPassword').val(),
                        role: $('#newRole').val()
                    };

                    if(!data.username || !data.password) {
                        Toast.show('用户名和密码不能为空', 'danger');
                        return;
                    }

                    $.ajax({
                        url: 'api/user.php',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(data),
                        success: response => {
                            if(response.success) {
                                Toast.show('用户添加成功');
                                this.loadUsers();
                            } else {
                                Toast.show(response.error || '添加失败', 'danger');
                            }
                        }
                    });
                }
            });
        });

        // 编辑用户
        $(document).on('click', '.edit-user', function() {
            const userId = $(this).data('id');
            const tr = $(this).closest('tr');
            const username = tr.find('td:first').text();
            const role = tr.find('td:eq(1)').text();

            Modal.show({
                title: '编辑用户',
                content: `
                    <div class="form-group mb-3">
                        <label>用户名</label>
                        <input type="text" class="form-control" id="editUsername" value="${username}" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label>角色</label>
                        <select class="form-control" id="editRole">
                            <option value="0">普通用户</option>
                            <option value="1">普通管理员</option>
                            <option value="2">用户管理员</option>
                        </select>
                    </div>
                `,
                onConfirm: () => {
                    $.ajax({
                        url: 'api/user.php',
                        type: 'PUT',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            id: userId,
                            role: $('#editRole').val()
                        }),
                        success: response => {
                            if(response.success) {
                                Toast.show('用户更新成功');
                                AdminRouter.loadUsers();
                            } else {
                                Toast.show(response.error || '更新失败', 'danger');
                            }
                        }
                    });
                }
            });
        });

        // 重置密码
        $(document).on('click', '.reset-pwd', function() {
            const userId = $(this).data('id');
            Modal.show({
                title: '重置密码',
                content: `
                    <div class="form-group">
                        <label>新密码</label>
                        <input type="password" class="form-control" id="newPassword">
                    </div>
                `,
                onConfirm: () => {
                    const password = $('#newPassword').val();
                    if(!password) {
                        Toast.show('密码不能为空', 'danger');
                        return;
                    }

                    $.ajax({
                        url: 'api/user.php',
                        type: 'PUT',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            id: userId,
                            password: password
                        }),
                        success: response => {
                            if(response.success) {
                                Toast.show('密码重置成功');
                            } else {
                                Toast.show(response.error || '重置失败', 'danger');
                            }
                        }
                    });
                }
            });
        });

        // 切换状态
        $(document).on('click', '.toggle-status', function() {
            const userId = $(this).data('id');
            const currentStatus = $(this).data('status');
            const newStatus = currentStatus == 1 ? 0 : 1;
            const action = currentStatus == 1 ? '禁用' : '启用';

            Modal.show({
                title: `确认${action}`,
                content: `确定要${action}该用户吗？`,
                onConfirm: () => {
                    $.ajax({
                        url: 'api/user.php',
                        type: 'PUT',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            id: userId,
                            status: newStatus
                        }),
                        success: response => {
                            if(response.success) {
                                Toast.show(`用户${action}成功`);
                                AdminRouter.loadUsers();
                            } else {
                                Toast.show(response.error || `${action}失败`, 'danger');
                            }
                        }
                    });
                }
            });
        });
    }
};

// 初始化管理页面
$(document).ready(() => AdminRouter.init());