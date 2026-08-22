import Swal from 'sweetalert2';

/**
 * Show a success alert
 * @param {string} title - Alert title
 * @param {string} message - Alert message
 */
export const showSuccess = (title = 'Thành công!', message = '') => {
    Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#10b981',
        timer: 2000,
        timerProgressBar: true
    });
};

/**
 * Show an error alert
 * @param {string} title - Alert title
 * @param {string} message - Alert message
 */
export const showError = (title = 'Lỗi!', message = '') => {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#ef4444'
    });
};

/**
 * Show a warning alert
 * @param {string} title - Alert title
 * @param {string} message - Alert message
 */
export const showWarning = (title = 'Cảnh báo!', message = '') => {
    Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#f59e0b'
    });
};

/**
 * Show a confirmation dialog
 * @param {string} title - Dialog title
 * @param {string} message - Dialog message
 * @param {string} confirmText - Confirm button text
 * @param {string} cancelText - Cancel button text
 * @returns {Promise<boolean>} - True if confirmed, false if cancelled
 */
export const showConfirm = async (
    title = 'Xác nhận',
    message = 'Bạn có chắc chắn muốn thực hiện hành động này?',
    confirmText = 'Xác nhận',
    cancelText = 'Hủy bỏ'
) => {
    const result = await Swal.fire({
        icon: 'question',
        title: title,
        text: message,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#ef4444',
        reverseButtons: true
    });
    return result.isConfirmed;
};

/**
 * Show a confirmation dialog with HTML content (e.g. bold name, warning text)
 * @param {string} title - Dialog title
 * @param {string} html - HTML content for the dialog body
 * @param {string} confirmText - Confirm button text
 * @param {string} cancelText - Cancel button text
 * @param {string} icon - Swal icon (warning|question|error|info|success)
 * @param {string} confirmColor - Confirm button color (hex)
 * @returns {Promise<boolean>} - True if confirmed, false if cancelled
 */
export const showConfirmHtml = async (
    title = 'Xác nhận',
    html = 'Bạn có chắc chắn muốn thực hiện hành động này?',
    confirmText = 'Xác nhận',
    cancelText = 'Hủy bỏ',
    icon = 'warning',
    confirmColor = '#dc2626'
) => {
    const result = await Swal.fire({
        icon: icon,
        title: title,
        html: html,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#6b7280',
        reverseButtons: true
    });
    return result.isConfirmed;
};

/**
 * Show a confirmation dialog for approving loan requests
 * @returns {Promise<boolean>} - True if confirmed, false if cancelled
 */
export const showApproveConfirm = async () => {
    const result = await Swal.fire({
        icon: 'question',
        title: 'Phê duyệt yêu cầu?',
        text: 'Phê duyệt yêu cầu này và giữ sách cho độc giả?',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check"></i> Phê duyệt',
        cancelButtonText: '<i class="fas fa-times"></i> Hủy bỏ',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#ef4444',
        reverseButtons: true
    });
    return result.isConfirmed;
};

/**
 * Show a confirmation dialog for rejecting loan requests
 * @returns {Promise<boolean>} - True if confirmed, false if cancelled
 */
export const showRejectConfirm = async () => {
    const result = await Swal.fire({
        icon: 'question',
        title: 'Từ chối yêu cầu?',
        text: 'Bạn có chắc chắn muốn từ chối yêu cầu này?',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-times"></i> Từ chối',
        cancelButtonText: '<i class="fas fa-undo"></i> Hủy bỏ',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        reverseButtons: true
    });
    return result.isConfirmed;
};

/**
 * Show a loading state
 * @param {string} title - Loading title
 */
export const showLoading = (title = 'Đang xử lý...') => {
    Swal.fire({
        title: title,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
};

/**
 * Close the current alert
 */
export const closeAlert = () => {
    Swal.close();
};

export default Swal;
