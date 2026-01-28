import Modal from './Modal';

export default function DeleteConfirmation({ show, onClose, onConfirm, title = 'Confirm Delete', message = 'Are you sure you want to delete this item? This action cannot be undone.' }) {
    return (
        <Modal show={show} onClose={onClose} title={title} maxWidth="md">
            <div className="mb-6">
                <p className="text-gray-600">{message}</p>
            </div>
            <div className="flex justify-end space-x-3">
                <button
                    onClick={onClose}
                    className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Cancel
                </button>
                <button
                    onClick={onConfirm}
                    className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                >
                    Delete
                </button>
            </div>
        </Modal>
    );
}
