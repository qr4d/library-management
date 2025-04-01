// 创建 js/store.js
const Store = {
    state: {
        currentUser: null,
        shelves: [],
        books: []
    },

    init() {
        // 初始化时加载必要数据
        if(window.isLoggedIn) {
            this.loadShelves();
        }
    },

    async loadShelves() {
        try {
            const shelves = await Utils.request('api/shelf.php');
            this.setShelves(shelves);
            return shelves;
        } catch(error) {
            console.error('Failed to load shelves:', error);
            return [];
        }
    },

    setUser(user) {
        this.state.currentUser = user;
        this.notify('user');
    },

    setShelves(shelves) {
        this.state.shelves = shelves;
        this.notify('shelves');
    },

    listeners: {},
    
    on(event, callback) {
        if(!this.listeners[event]) {
            this.listeners[event] = [];
        }
        this.listeners[event].push(callback);
    },
    
    notify(event) {
        if(this.listeners[event]) {
            this.listeners[event].forEach(cb => cb(this.state[event]));
        }
    }
};