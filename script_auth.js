// Auth and Cart Management System
$(document).ready(function() {
    console.log('Bolsi website loaded with authentication');

    // Global variables
    window.currentUser = null;
    let cart = [];
    let cartCount = 0;

    // ===== AUTHENTICATION FUNCTIONS =====

    function checkLoginStatus() {
        $.ajax({
            url: 'check_session.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.logged_in) {
                    window.currentUser = response.user;
                    updateUIForLoggedInUser();
                    loadCart();
                } else {
                    window.currentUser = null;
                    updateUIForGuest();
                }
            },
            error: function() {
                console.log('Error checking session');
                updateUIForGuest();
            }
        });
    }

    function updateUIForLoggedInUser() {
        // Update account button
        const $accountBtn = $('#accountBtn');
        $accountBtn.html(`
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <circle cx="12" cy="8" r="4"/>
                <path d="M4 20c0-4 3.5-7 8-7s8 3 8 7"/>
            </svg>
        `);
        $accountBtn.attr('title', window.currentUser.name || window.currentUser.email);
        
        // Show logout option when clicking account
        $accountBtn.off('click').on('click', function() {
            showAccountMenu();
        });
    }

    function updateUIForGuest() {
        const $accountBtn = $('#accountBtn');
        $accountBtn.html(`
            <svg width="24" height="24" viewBox="0 0 24 24">
                <circle cx="12" cy="8" r="4" stroke="currentColor" fill="none" stroke-width="1.5"/>
                <path d="M4 20c0-4 3.5-7 8-7s8 3 8 7" stroke="currentColor" fill="none" stroke-width="1.5"/>
            </svg>
        `);
        $accountBtn.attr('title', 'Iniciar sesión');
        $accountBtn.off('click').on('click', function() {
            openLoginModal();
        });
    }

    function showAccountMenu() {
        // Create account dropdown menu
        if ($('#accountMenu').length) {
            $('#accountMenu').remove();
        }

        const menu = $(`
            <div id="accountMenu" class="account-dropdown">
                <div class="account-dropdown-content">
                    <p class="account-user-name">${window.currentUser.name || 'Usuario'}</p>
                    <p class="account-user-email">${window.currentUser.email}</p>
                    <hr>
                    <button class="account-menu-item" id="myOrdersBtn">Mis Pedidos</button>
                    <button class="account-menu-item" id="myProfileBtn">Mi Perfil</button>
                    <button class="account-menu-item" id="logoutBtn">Cerrar Sesión</button>
                </div>
            </div>
        `);

        $('body').append(menu);
        
        setTimeout(() => menu.addClass('active'), 10);

        // Close menu when clicking outside
        $(document).on('click.accountMenu', function(e) {
            if (!$(e.target).closest('#accountMenu, #accountBtn').length) {
                closeAccountMenu();
            }
        });

        // Logout button
        $('#logoutBtn').on('click', function() {
            logout();
        });

        // Other menu items
        $('#myOrdersBtn, #myProfileBtn').on('click', function() {
            showNotification('Función próximamente disponible');
            closeAccountMenu();
        });
    }

    function closeAccountMenu() {
        $('#accountMenu').removeClass('active');
        setTimeout(() => $('#accountMenu').remove(), 300);
        $(document).off('click.accountMenu');
    }

    function logout() {
        $.ajax({
            url: 'logout.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.currentUser = null;
                    cart = [];
                    cartCount = 0;
                    updateCartCount(0);
                    renderCart();
                    updateUIForGuest();
                    closeAccountMenu();
                    showNotification('Sesión cerrada correctamente');
                }
            },
            error: function() {
                showNotification('Error al cerrar sesión', 'error');
            }
        });
    }

    // ===== LOGIN MODAL =====
    
    const $loginModal = $('#loginModal');
    const $closeLogin = $('#closeLogin');
    
    function openLoginModal() {
        $loginModal.addClass('active');
        $('body').css('overflow', 'hidden');
    }

    function closeLoginModal() {
        $loginModal.removeClass('active');
        $('body').css('overflow', 'auto');
    }

    $closeLogin.on('click', closeLoginModal);

    // Close modal when clicking outside
    $loginModal.on('click', function(e) {
        if ($(e.target).is($loginModal)) {
            closeLoginModal();
        }
    });

    // Toggle password visibility
    $(document).on('click', '.toggle-password', function() {
        const $input = $(this).siblings('input');
        const type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        
        // Cambiar icono
        const $icon = $(this).find('svg');
        if (type === 'text') {
            $icon.html('<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" stroke="currentColor" fill="none" stroke-width="1.5"/>');
        } else {
            $icon.html('<path d="M12 5C5.636 5 2 12 2 12s3.636 7 10 7 10-7 10-7-3.636-7-10-7z" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>');
        }
    });

    // Login form submission
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        const email = $('#loginEmail').val().trim();
        const password = $('#loginPassword').val();

        if (!email || !password) {
            showNotification('Por favor completa todos los campos', 'error');
            return;
        }

        // Show loading
        const $submitBtn = $(this).find('button[type="submit"]');
        const originalText = $submitBtn.text();
        $submitBtn.prop('disabled', true).text('Iniciando sesión...');

        $.ajax({
            url: 'login.php',
            method: 'POST',
            data: { email, password },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.currentUser = response.user;
                    updateUIForLoggedInUser();
                    closeLoginModal();
                    showNotification(response.message);
                    
                    // Load cart if exists
                    if (response.cart_items && response.cart_items.length > 0) {
                        loadCartItems(response.cart_items);
                    } else {
                        loadCart();
                    }
                    
                    // Reset form
                    $('#loginForm')[0].reset();
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('Error de conexión. Intenta nuevamente.', 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Register button - Show register form
    $('#registerBtn').on('click', function() {
        showRegisterForm();
    });

    function showRegisterForm() {
        const $loginSection = $('.login-section');
        const $registerSection = $('.register-section');
        const $divider = $('.login-divider');

        // Hide login and register sections
        $loginSection.hide();
        $divider.hide();
        $registerSection.hide();

        // Create register form
        const registerForm = $(`
            <div class="register-form-section">
                <button type="button" class="back-to-login" id="backToLogin">← Volver al inicio de sesión</button>
                <h3 class="register-subtitle">Crear una Cuenta</h3>
                <form class="register-form" id="registerForm">
                    <div class="form-group">
                        <label for="registerName">Nombre completo *</label>
                        <input type="text" id="registerName" required>
                    </div>
                    <div class="form-group">
                        <label for="registerEmail">Email *</label>
                        <input type="email" id="registerEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="registerPhone">Teléfono</label>
                        <input type="tel" id="registerPhone">
                    </div>
                    <div class="form-group">
                        <label for="registerPassword">Contraseña * (mínimo 6 caracteres)</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="registerPassword" required minlength="6">
                            <button type="button" class="toggle-password">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5C5.636 5 2 12 2 12s3.636 7 10 7 10-7 10-7-3.636-7-10-7z" stroke="currentColor" stroke-width="1.5"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="registerPasswordConfirm">Confirmar contraseña *</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="registerPasswordConfirm" required minlength="6">
                            <button type="button" class="toggle-password">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5C5.636 5 2 12 2 12s3.636 7 10 7 10-7 10-7-3.636-7-10-7z" stroke="currentColor" stroke-width="1.5"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p class="form-note">* Campos obligatorios</p>
                    <button type="submit" class="login-submit-btn">Crear cuenta</button>
                </form>
            </div>
        `);

        $('.login-modal-content').append(registerForm);

        // Back to login
        $('#backToLogin').on('click', function() {
            $('.register-form-section').remove();
            $loginSection.show();
            $divider.show();
            $registerSection.show();
        });

        // Handle register form submission
        $('#registerForm').on('submit', function(e) {
            e.preventDefault();

            const name = $('#registerName').val().trim();
            const email = $('#registerEmail').val().trim();
            const phone = $('#registerPhone').val().trim();
            const password = $('#registerPassword').val();
            const passwordConfirm = $('#registerPasswordConfirm').val();

            // Validation
            if (!name) {
                showNotification('El nombre es obligatorio', 'error');
                return;
            }

            if (!email) {
                showNotification('El email es obligatorio', 'error');
                return;
            }

            if (!password || password.length < 6) {
                showNotification('La contraseña debe tener al menos 6 caracteres', 'error');
                return;
            }

            if (password !== passwordConfirm) {
                showNotification('Las contraseñas no coinciden', 'error');
                return;
            }

            const $submitBtn = $(this).find('button[type="submit"]');
            const originalText = $submitBtn.text();
            $submitBtn.prop('disabled', true).text('Creando cuenta...');

            $.ajax({
                url: 'register.php',
                method: 'POST',
                data: { 
                    name: name, 
                    email: email, 
                    phone: phone, 
                    password: password 
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.currentUser = response.user;
                        updateUIForLoggedInUser();
                        closeLoginModal();
                        showNotification('¡Cuenta creada exitosamente!');
                        
                        // Reset form and show login section
                        $('#registerForm')[0].reset();
                        $('.register-form-section').remove();
                        $('.login-section').show();
                        $('.login-divider').show();
                        $('.register-section').show();
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Registration error:', error);
                    showNotification('Error de conexión. Intenta nuevamente.', 'error');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });
    }

    // ===== CART FUNCTIONS =====

    const $cartCount = $('.cart-count');
    const $cartBtn = $('.cart-btn');
    const $sideCart = $('#sideCart');
    const $closeCart = $('#closeCart');
    const $continueShoppingBtn = $('#continueShoppingBtn');

    function updateCartCount(newCount) {
        cartCount = newCount;
        $cartCount.text(cartCount);
        $cartCount.css('transform', 'scale(1.5)');
        setTimeout(() => {
            $cartCount.css('transform', 'scale(1)');
        }, 300);
    }

    // Open cart
    $cartBtn.on('click', function() {
        $sideCart.addClass('active');
        $('body').css('overflow', 'hidden');
    });

    // Close cart
    $closeCart.on('click', function() {
        $sideCart.removeClass('active');
        $('body').css('overflow', 'auto');
    });

    // Continue shopping
    $continueShoppingBtn.on('click', function() {
        $sideCart.removeClass('active');
        $('body').css('overflow', 'auto');
    });

    // Close cart when clicking outside
    $sideCart.on('click', function(e) {
        if ($(e.target).is($sideCart)) {
            $sideCart.removeClass('active');
            $('body').css('overflow', 'auto');
        }
    });

    // Load cart from database
    function loadCart() {
        if (!window.currentUser) return;

        $.ajax({
            url: 'cart.php?action=get',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    loadCartItems(response.items);
                }
            },
            error: function() {
                console.log('Error loading cart');
            }
        });
    }

    function loadCartItems(items) {
        cart = items.map(item => ({
            id: item.product_id,
            itemId: item.id,
            name: item.name,
            price: parseFloat(item.price),
            image: item.image,
            quantity: item.quantity
        }));
        
        updateCartCount(cart.reduce((sum, item) => sum + item.quantity, 0));
        renderCart();
    }

    // Add product to cart (global function)
    window.addToCart = function(product) {
        if (!window.currentUser) {
            showNotification('Debes iniciar sesión para agregar productos al carrito', 'error');
            openLoginModal();
            return;
        }

        $.ajax({
            url: 'cart.php?action=add',
            method: 'POST',
            data: {
                product_id: product.id,
                quantity: 1
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    loadCartItems(response.items);
                    $sideCart.addClass('active');
                    $('body').css('overflow', 'hidden');
                    showNotification(`${product.name} añadido al carrito`);
                } else {
                    if (response.require_login) {
                        openLoginModal();
                    }
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('Error al agregar al carrito', 'error');
            }
        });
    };

    // Remove from cart
    function removeFromCart(itemId) {
        if (!window.currentUser) return;

        $.ajax({
            url: 'cart.php?action=remove',
            method: 'POST',
            data: { item_id: itemId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    loadCartItems(response.items);
                    showNotification('Producto eliminado del carrito');
                }
            }
        });
    }

    // Update cart quantity
    function updateCartQuantity(itemId, quantity) {
        if (!window.currentUser) return;

        $.ajax({
            url: 'cart.php?action=update',
            method: 'POST',
            data: {
                item_id: itemId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    loadCartItems(response.items);
                }
            }
        });
    }

    // Render cart
    function renderCart() {
        const $cartItems = $('#cartItems');
        const $emptyCart = $('#emptyCart');
        const $cartFooter = $('#cartFooter');
        
        $cartItems.find('.cart-item').remove();
        
        if (cart.length === 0) {
            $emptyCart.show();
            $cartFooter.hide();
            return;
        }
        
        $emptyCart.hide();
        $cartFooter.show();
        
        cart.forEach(item => {
            const cartItem = $(`
                <div class="cart-item" data-item-id="${item.itemId}">
                    <div class="cart-item-image">
                        <img src="${item.image}" alt="${item.name}">
                    </div>
                    <div class="cart-item-details">
                        <h4 class="cart-item-name">${item.name}</h4>
                        <p class="cart-item-price">€${item.price.toFixed(2)}</p>
                        <div class="cart-item-quantity">
                            <button class="qty-btn minus" data-item-id="${item.itemId}">-</button>
                            <span class="qty-value">${item.quantity}</span>
                            <button class="qty-btn plus" data-item-id="${item.itemId}">+</button>
                        </div>
                    </div>
                    <button class="cart-item-remove" data-item-id="${item.itemId}">
                        <svg width="20" height="20" viewBox="0 0 24 24">
                            <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
            `);
            
            $cartItems.append(cartItem);
        });
        
        // Calculate and update total
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        $('#totalAmount').text(`€${total.toFixed(2)}`);
        
        // Attach event handlers for quantity buttons
        $(document).off('click', '.qty-btn.minus').on('click', '.qty-btn.minus', function() {
            const itemId = $(this).data('item-id');
            const item = cart.find(i => i.itemId === itemId);
            if (item && item.quantity > 1) {
                updateCartQuantity(itemId, item.quantity - 1);
            }
        });
        
        $(document).off('click', '.qty-btn.plus').on('click', '.qty-btn.plus', function() {
            const itemId = $(this).data('item-id');
            const item = cart.find(i => i.itemId === itemId);
            if (item) {
                updateCartQuantity(itemId, item.quantity + 1);
            }
        });
        
        // Attach event handlers for remove buttons
        $(document).off('click', '.cart-item-remove').on('click', '.cart-item-remove', function() {
            const itemId = $(this).data('item-id');
            removeFromCart(itemId);
        });
    }

    // Checkout button
    $('#checkoutBtn').on('click', function() {
        if (!window.currentUser) {
            showNotification('Debes iniciar sesión para continuar', 'error');
            openLoginModal();
            return;
        }
        
        if (cart.length === 0) {
            showNotification('Tu carrito está vacío', 'error');
            return;
        }
        
        showNotification('Función de pago próximamente disponible');
    });

    // ===== NOTIFICATION SYSTEM =====
    
    window.showNotification = function(message, type = 'success') {
        // Remove existing notifications
        $('.notification').remove();
        
        const notification = $(`
            <div class="notification ${type}">
                <p>${message}</p>
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(() => {
            notification.addClass('show');
        }, 100);
        
        setTimeout(() => {
            notification.removeClass('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // ===== MENU FUNCTIONALITY =====
    
    const $sideMenu = $('#sideMenu');
    const $menuBtn = $('#menuBtn');
    const $closeMenu = $('#closeMenu');

    $menuBtn.on('click', function() {
        $sideMenu.addClass('active');
        $('body').css('overflow', 'hidden');
    });

    $closeMenu.on('click', function() {
        $sideMenu.removeClass('active');
        $('body').css('overflow', 'auto');
    });

    // Close menu when clicking outside
    $(document).on('click', function(e) {
        if ($sideMenu.hasClass('active') && 
            !$(e.target).closest('#sideMenu').length && 
            !$(e.target).closest('#menuBtn').length) {
            $sideMenu.removeClass('active');
            $('body').css('overflow', 'auto');
        }
    });

    // ===== SEARCH FUNCTIONALITY =====
    
    const $searchModal = $('#searchModal');
    const $searchBtn = $('#searchBtn');
    const $closeSearch = $('#closeSearch');
    const $cancelSearch = $('#cancelSearch');
    const $searchForm = $('#searchForm');
    const $searchInput = $('#searchInput');

    $searchBtn.on('click', function() {
        $searchModal.addClass('active');
        $('body').css('overflow', 'hidden');
        setTimeout(() => $searchInput.focus(), 300);
    });

    function closeSearchModal() {
        $searchModal.removeClass('active');
        $('body').css('overflow', 'auto');
        $searchInput.val('');
    }

    $closeSearch.on('click', closeSearchModal);
    $cancelSearch.on('click', closeSearchModal);

    // Search form submission
    $searchForm.on('submit', function(e) {
        e.preventDefault();
        const query = $searchInput.val().trim();
        
        if (query.length < 2) {
            showNotification('Introduce al menos 2 caracteres', 'error');
            return;
        }

        performSearch(query);
    });

    // Suggestion tags
    $('.suggestion-tag').on('click', function() {
        const query = $(this).data('query');
        $searchInput.val(query);
        performSearch(query);
    });

    function performSearch(query) {
        $.ajax({
            url: 'search_new.php',
            method: 'GET',
            data: { q: query },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    displayProducts(response.data);
                    closeSearchModal();
                    showNotification(response.message);
                    
                    // Scroll to products
                    $('html, body').animate({
                        scrollTop: $('#products').offset().top - 100
                    }, 500);
                } else {
                    showNotification(response.message || 'No se encontraron productos', 'error');
                }
            },
            error: function() {
                showNotification('Error al realizar la búsqueda', 'error');
            }
        });
    }

    // ===== PRODUCTS LOADING =====
    
    function loadProducts(category = 'todos') {
        $.ajax({
            url: 'data_products.php',
            method: 'GET',
            data: { category: category },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayProducts(response.data);
                }
            },
            error: function() {
                console.error('Error loading products');
            }
        });
    }

    function displayProducts(products) {
        const $productsGrid = $('#productsGrid');
        $productsGrid.empty();
        
        products.forEach(product => {
            const productCard = $(`
                <div class="product-card" data-product-id="${product.id}">
                    <div class="product-image">
                        <img src="${product.image}" alt="${product.name}">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">${product.name}</h3>
                        <p class="product-price">€${parseFloat(product.price).toFixed(2)}</p>
                        <button class="add-to-cart-btn" onclick="addToCart({
                            id: ${product.id},
                            name: '${product.name.replace(/'/g, "\\'")}',
                            price: ${product.price},
                            image: '${product.image}'
                        })">Añadir al Carrito</button>
                    </div>
                </div>
            `);
            
            $productsGrid.append(productCard);
        });
    }

    // Load products on page load
    loadProducts();

    // Category filter
    $('.menu-list a[data-category]').on('click', function(e) {
        e.preventDefault();
        const category = $(this).data('category');
        loadProducts(category);
        $sideMenu.removeClass('active');
        $('body').css('overflow', 'auto');
        
        // Scroll to products
        $('html, body').animate({
            scrollTop: $('#products').offset().top - 100
        }, 500);
    });

    // Occasion filter
    $('.menu-list a[data-occasion]').on('click', function(e) {
        e.preventDefault();
        const occasion = $(this).data('occasion');
        
        $.ajax({
            url: 'data_products.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const filteredProducts = response.data.filter(product => 
                        product.occasion === occasion
                    );
                    displayProducts(filteredProducts);
                    showNotification(`Mostrando productos para: ${occasion}`);
                }
            }
        });
        
        $sideMenu.removeClass('active');
        $('body').css('overflow', 'auto');
        
        // Scroll to products
        $('html, body').animate({
            scrollTop: $('#products').offset().top - 100
        }, 500);
    });

    // ===== NEWSLETTER =====
    
    $('#newsletterForm').on('submit', function(e) {
        e.preventDefault();
        
        const email = $('#emailInput').val().trim();
        
        $.ajax({
            url: 'newsletter.php',
            method: 'POST',
            data: { email: email },
            dataType: 'json',
            success: function(response) {
                const $message = $('#newsletterMessage');
                $message.text(response.message);
                $message.css('color', response.success ? '#2d5016' : '#c41e3a');
                $message.fadeIn();
                
                if (response.success) {
                    $('#emailInput').val('');
                }
                
                setTimeout(() => {
                    $message.fadeOut();
                }, 4000);
            },
            error: function() {
                $('#newsletterMessage').text('Error al suscribirse').css('color', '#c41e3a').fadeIn();
            }
        });
    });

    // Wishlist button
    $('#wishlistBtn').on('click', function() {
        if (!window.currentUser) {
            showNotification('Debes iniciar sesión para ver tu lista de deseos', 'error');
            openLoginModal();
        } else {
            showNotification('Función próximamente disponible');
        }
    });

    // Contact button
    $('#contactBtn').on('click', function() {
        showNotification('Teléfono: +34 900 123 456');
    });

    // Forgot password link
    $('.forgot-password').on('click', function(e) {
        e.preventDefault();
        showNotification('Función de recuperación de contraseña próximamente disponible');
    });

    // Alternative login link
    $('.alternative-login a').on('click', function(e) {
        e.preventDefault();
        showNotification('Función de enlace de acceso único próximamente disponible');
    });

    // Check login status on page load
    checkLoginStatus();

    // Initialize other page features
    initializePageFeatures();
});

function initializePageFeatures() {
    // Hero button functionality
    $('.hero-btn').on('click', function(e) {
        e.preventDefault();
        const target = $(this).attr('href');
        if (target === '#products') {
            $('html, body').animate({
                scrollTop: $('#products').offset().top - 100
            }, 800);
        }
    });

    // Explore button
    $('#exploreBtn').on('click', function() {
        loadProducts('todos');
        $('html, body').animate({
            scrollTop: $('#products').offset().top - 100
        }, 800);
    });

    // Collections button
    $('#collectionsBtn').on('click', function() {
        loadProducts();
        $('html, body').animate({
            scrollTop: $('#products').offset().top - 100
        }, 800);
    });

    // Banner button
    $('.banner-btn').on('click', function() {
        loadProducts();
        $('html, body').animate({
            scrollTop: $('#products').offset().top - 100
        }, 800);
    });

    // Collection links
    $('.collection-link').on('click', function(e) {
        e.preventDefault();
        const category = $(this).closest('.collection-info').find('h3').text().toLowerCase();
        if (category.includes('clásica')) {
            loadProducts('bandolera');
        } else if (category.includes('limitada')) {
            loadProducts('tote');
        }
        $('html, body').animate({
            scrollTop: $('#products').offset().top - 100
        }, 800);
    });

    // Sustainability button
    $('.sustainability-btn').on('click', function() {
        showNotification('Más información sobre sostenibilidad próximamente');
    });
}