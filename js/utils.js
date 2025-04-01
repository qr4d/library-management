// 创建 js/utils.js
const Utils = {
    // API 请求封装
    async request(url, options = {}) {
        const defaultOptions = {
            contentType: 'application/json',
            dataType: 'json'
        };

        try {
            const response = await $.ajax({ 
                ...defaultOptions, 
                ...options, 
                url 
            });
            return response;
        } catch(error) {
            Toast.show(error.responseJSON?.error || '请求失败', 'danger');
            throw error;
        }
    },

    // 防抖函数
    debounce(fn, delay = 300) {
        let timer = null;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    },

    // 格式化日期
    formatDate(date) {
        return new Date(date).toLocaleDateString('zh-CN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    }
};