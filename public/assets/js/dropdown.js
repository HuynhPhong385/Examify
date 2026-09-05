(() => {

    $(document).ready(function () {

        const $selector = $('#roleSelector');
        const $button = $('#roleButton');


        // ==========================
        // MỞ / ĐÓNG DROPDOWN
        // ==========================

        $button.on('click', function (e) {

            e.stopPropagation();

            $selector.toggleClass('open');


        });


        // ==========================
        // CHỌN ROLE
        // ==========================

        $('.role-item').on('click', async function () {

            const $item = $(this);

            const role = $item.data('role');
            const name = $item.data('name');
            const username = $item.data('username');

            const roles = {
                'admin': {
                    'email': 'admin@examify.local',
                    'password': 'Admin@123'
                },
                'teacher': {
                    'email': 'teacher@examify.local',
                    'password': 'Teacher@123'
                },
                'student': {
                    'email': 'student@examify.local',
                    'password': 'Student@123'
                }
            };
            if (role != 'admin') {


                $button.addClass('loading');
                await Examify.api('/auth/login', { method: 'POST', body: JSON.stringify(roles[role]) })
                location.reload();
                $('#currentRoleName').text(name);
                $('#currentRoleUsername').text(username);


                // Xóa trạng thái active
                $('.role-item').removeClass('active');


                // Active role vừa chọn
                $item.addClass('active');


                // Đóng dropdown
                await $selector.removeClass('open');


                console.log(
                    'Đã chuyển sang role:',
                    role
                );
                $button.removeClass('loading');
            };
            /*$.ajax({

                url: '../../api/index.php',

                type: 'POST',

                dataType: 'json',

                data: {
                    role: role
                },

                success: function (response) {

                    if (response.success) {

                        // Cập nhật nút
                        

                    } else {

                        alert(
                            response.message ||
                            'Không thể chuyển chế độ.'
                        );

                    }

                },

                error: function () {

                    alert(
                        'Có lỗi khi kết nối đến server.'
                    );

                },

                complete: function () {

                    

                }

            });*/

        });


        // ==========================
        // CLICK NGOÀI -> ĐÓNG
        // ==========================

        $(document).on('click', function (e) {

            if (
                !$(e.target).closest('#roleSelector').length
            ) {

                $selector.removeClass('open');

            }

        });

    });
})();