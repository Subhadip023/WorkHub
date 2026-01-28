import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Admin/Card';
import DataTable from '@/Components/Admin/DataTable';
import Modal from '@/Components/Admin/Modal';
import FormInput from '@/Components/Admin/FormInput';
import FormTextarea from '@/Components/Admin/FormTextarea';
import FormSelect from '@/Components/Admin/FormSelect';
import DeleteConfirmation from '@/Components/Admin/DeleteConfirmation';
import Badge from '@/Components/Admin/Badge';
import Pagination from '@/Components/Admin/Pagination';

export default function TasksIndex({ tasks, projects = [] }) {
    const [showModal, setShowModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [editingTask, setEditingTask] = useState(null);
    const [deletingTask, setDeletingTask] = useState(null);
    const [formData, setFormData] = useState({
        title: '',
        project_id: '',
        description: '',
        assigned_to: '',
        priority: 'medium',
        status: 'todo',
        due_date: '',
    });

    // Sample data for demonstration
    const sampleProjects = projects.length > 0 ? projects : [
        { id: 1, name: 'Website Redesign' },
        { id: 2, name: 'Mobile App Development' },
        { id: 3, name: 'Marketing Campaign' },
    ];

    const sampleTasks = tasks?.data || [
        { id: 1, title: 'Design homepage mockup', project: 'Website Redesign', priority: 'high', status: 'in_progress', due_date: '2024-01-20' },
        { id: 2, title: 'Setup development environment', project: 'Mobile App Development', priority: 'high', status: 'completed', due_date: '2024-01-15' },
        { id: 3, title: 'Create social media content', project: 'Marketing Campaign', priority: 'medium', status: 'todo', due_date: '2024-01-25' },
        { id: 4, title: 'Review brand guidelines', project: 'Website Redesign', priority: 'low', status: 'todo', due_date: '2024-01-22' },
        { id: 5, title: 'Test user authentication', project: 'Mobile App Development', priority: 'high', status: 'in_progress', due_date: '2024-01-18' },
        { id: 6, title: 'Write API documentation', project: 'Mobile App Development', priority: 'medium', status: 'review', due_date: '2024-01-23' },
        { id: 7, title: 'Implement payment gateway', project: 'E-commerce Platform', priority: 'urgent', status: 'in_progress', due_date: '2024-01-19' },
        { id: 8, title: 'Design email templates', project: 'Marketing Campaign', priority: 'medium', status: 'todo', due_date: '2024-01-26' },
        { id: 9, title: 'Database optimization', project: 'Website Redesign', priority: 'high', status: 'blocked', due_date: '2024-01-21' },
        { id: 10, title: 'Security audit', project: 'Mobile App Development', priority: 'urgent', status: 'todo', due_date: '2024-01-17' },
    ];

    const priorityOptions = [
        { value: 'low', label: 'Low' },
        { value: 'medium', label: 'Medium' },
        { value: 'high', label: 'High' },
        { value: 'urgent', label: 'Urgent' },
    ];

    const statusOptions = [
        { value: 'todo', label: 'To Do' },
        { value: 'in_progress', label: 'In Progress' },
        { value: 'review', label: 'In Review' },
        { value: 'completed', label: 'Completed' },
        { value: 'blocked', label: 'Blocked' },
    ];

    const projectOptions = sampleProjects.map(project => ({
        value: project.id,
        label: project.name,
    }));

    const getPriorityVariant = (priority) => {
        const variants = {
            low: 'success',
            medium: 'warning',
            high: 'danger',
            urgent: 'danger',
        };
        return variants[priority] || 'gray';
    };

    const getStatusVariant = (status) => {
        const variants = {
            todo: 'gray',
            in_progress: 'primary',
            review: 'info',
            completed: 'success',
            blocked: 'danger',
        };
        return variants[status] || 'gray';
    };

    const columns = [
        { label: 'Task', key: 'title' },
        { label: 'Project', key: 'project' },
        {
            label: 'Priority',
            key: 'priority',
            badge: true,
            badgeVariant: getPriorityVariant,
            render: (value) => value.toUpperCase()
        },
        {
            label: 'Status',
            key: 'status',
            badge: true,
            badgeVariant: getStatusVariant,
            render: (value) => value.replace('_', ' ').toUpperCase()
        },
        { label: 'Due Date', key: 'due_date' },
    ];

    const handleCreate = () => {
        setEditingTask(null);
        setFormData({ title: '', project_id: '', description: '', assigned_to: '', priority: 'medium', status: 'todo', due_date: '' });
        setShowModal(true);
    };

    const handleEdit = (task) => {
        setEditingTask(task);
        setFormData({
            title: task.title,
            project_id: task.project_id || '',
            description: task.description || '',
            assigned_to: task.assigned_to || '',
            priority: task.priority,
            status: task.status,
            due_date: task.due_date,
        });
        setShowModal(true);
    };

    const handleDelete = (task) => {
        setDeletingTask(task);
        setShowDeleteModal(true);
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        if (editingTask) {
            // router.put(route('tasks.update', editingTask.id), formData);
            console.log('Update task:', editingTask.id, formData);
        } else {
            // router.post(route('tasks.store'), formData);
            console.log('Create task:', formData);
        }

        setShowModal(false);
    };

    const confirmDelete = () => {
        // router.delete(route('tasks.destroy', deletingTask.id));
        console.log('Delete task:', deletingTask.id);
        setShowDeleteModal(false);
    };

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    return (
        <AdminLayout>
            <Head title="Tasks" />

            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Tasks</h1>
                    <p className="text-gray-600 mt-1">Manage your tasks</p>
                </div>
                <button
                    onClick={handleCreate}
                    className="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Task</span>
                </button>
            </div>

            <Card padding="p-0">
                <DataTable
                    columns={columns}
                    data={sampleTasks}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    emptyMessage="No tasks found. Click 'Add Task' to create one."
                />
                <Pagination links={tasks?.links} meta={tasks?.meta} />
            </Card>

            {/* Create/Edit Modal */}
            <Modal
                show={showModal}
                onClose={() => setShowModal(false)}
                title={editingTask ? 'Edit Task' : 'Add Task'}
                maxWidth="2xl"
            >
                <form onSubmit={handleSubmit}>
                    <FormInput
                        label="Task Title"
                        name="title"
                        value={formData.title}
                        onChange={handleChange}
                        required
                        placeholder="Enter task title"
                    />

                    <FormSelect
                        label="Project"
                        name="project_id"
                        value={formData.project_id}
                        onChange={handleChange}
                        options={projectOptions}
                        required
                        placeholder="Select a project"
                    />

                    <FormTextarea
                        label="Description"
                        name="description"
                        value={formData.description}
                        onChange={handleChange}
                        placeholder="Enter task description"
                        rows={3}
                    />

                    <FormInput
                        label="Assigned To"
                        name="assigned_to"
                        value={formData.assigned_to}
                        onChange={handleChange}
                        placeholder="Enter assignee name or email"
                    />

                    <div className="grid grid-cols-3 gap-4">
                        <FormSelect
                            label="Priority"
                            name="priority"
                            value={formData.priority}
                            onChange={handleChange}
                            options={priorityOptions}
                            required
                        />

                        <FormSelect
                            label="Status"
                            name="status"
                            value={formData.status}
                            onChange={handleChange}
                            options={statusOptions}
                            required
                        />

                        <FormInput
                            label="Due Date"
                            name="due_date"
                            type="date"
                            value={formData.due_date}
                            onChange={handleChange}
                            required
                        />
                    </div>

                    <div className="flex justify-end space-x-3 mt-6">
                        <button
                            type="button"
                            onClick={() => setShowModal(false)}
                            className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            {editingTask ? 'Update' : 'Create'}
                        </button>
                    </div>
                </form>
            </Modal>

            {/* Delete Confirmation */}
            <DeleteConfirmation
                show={showDeleteModal}
                onClose={() => setShowDeleteModal(false)}
                onConfirm={confirmDelete}
                title="Delete Task"
                message={`Are you sure you want to delete "${deletingTask?.title}"? This action cannot be undone.`}
            />
        </AdminLayout>
    );
}
