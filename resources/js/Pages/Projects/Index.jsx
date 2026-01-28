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

export default function ProjectsIndex({ projects, companies = [] }) {
    const [showModal, setShowModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [editingProject, setEditingProject] = useState(null);
    const [deletingProject, setDeletingProject] = useState(null);
    const [formData, setFormData] = useState({
        name: '',
        company_id: '',
        description: '',
        start_date: '',
        end_date: '',
        status: 'planning',
        budget: '',
    });

    // Sample data for demonstration
    const sampleCompanies = companies.length > 0 ? companies : [
        { id: 1, name: 'Tech Corp' },
        { id: 2, name: 'Design Studio' },
        { id: 3, name: 'Marketing Pro' },
    ];

    const sampleProjects = projects?.data || [
        { id: 1, name: 'Website Redesign', company: 'Tech Corp', status: 'in_progress', start_date: '2024-01-15', budget: '$50,000' },
        { id: 2, name: 'Mobile App Development', company: 'Design Studio', status: 'planning', start_date: '2024-02-01', budget: '$120,000' },
        { id: 3, name: 'Marketing Campaign', company: 'Marketing Pro', status: 'completed', start_date: '2023-12-01', budget: '$30,000' },
        { id: 4, name: 'E-commerce Platform', company: 'Tech Corp', status: 'in_progress', start_date: '2024-01-20', budget: '$200,000' },
        { id: 5, name: 'Brand Identity', company: 'Design Studio', status: 'planning', start_date: '2024-02-15', budget: '$45,000' },
        { id: 6, name: 'SEO Optimization', company: 'Marketing Pro', status: 'in_progress', start_date: '2024-01-10', budget: '$25,000' },
        { id: 7, name: 'Cloud Migration', company: 'Tech Corp', status: 'on_hold', start_date: '2024-03-01', budget: '$150,000' },
        { id: 8, name: 'UI/UX Redesign', company: 'Design Studio', status: 'completed', start_date: '2023-11-15', budget: '$60,000' },
    ];

    const statusOptions = [
        { value: 'planning', label: 'Planning' },
        { value: 'in_progress', label: 'In Progress' },
        { value: 'on_hold', label: 'On Hold' },
        { value: 'completed', label: 'Completed' },
        { value: 'cancelled', label: 'Cancelled' },
    ];

    const companyOptions = sampleCompanies.map(company => ({
        value: company.id,
        label: company.name,
    }));

    const getStatusVariant = (status) => {
        const variants = {
            planning: 'info',
            in_progress: 'primary',
            on_hold: 'warning',
            completed: 'success',
            cancelled: 'danger',
        };
        return variants[status] || 'gray';
    };

    const columns = [
        { label: 'Project Name', key: 'name' },
        { label: 'Company', key: 'company' },
        {
            label: 'Status',
            key: 'status',
            badge: true,
            badgeVariant: getStatusVariant,
            render: (value) => value.replace('_', ' ').toUpperCase()
        },
        { label: 'Start Date', key: 'start_date' },
        { label: 'Budget', key: 'budget' },
    ];

    const handleCreate = () => {
        setEditingProject(null);
        setFormData({ name: '', company_id: '', description: '', start_date: '', end_date: '', status: 'planning', budget: '' });
        setShowModal(true);
    };

    const handleEdit = (project) => {
        setEditingProject(project);
        setFormData({
            name: project.name,
            company_id: project.company_id || '',
            description: project.description || '',
            start_date: project.start_date,
            end_date: project.end_date || '',
            status: project.status,
            budget: project.budget,
        });
        setShowModal(true);
    };

    const handleDelete = (project) => {
        setDeletingProject(project);
        setShowDeleteModal(true);
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        if (editingProject) {
            // router.put(route('projects.update', editingProject.id), formData);
            console.log('Update project:', editingProject.id, formData);
        } else {
            // router.post(route('projects.store'), formData);
            console.log('Create project:', formData);
        }

        setShowModal(false);
    };

    const confirmDelete = () => {
        // router.delete(route('projects.destroy', deletingProject.id));
        console.log('Delete project:', deletingProject.id);
        setShowDeleteModal(false);
    };

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    return (
        <AdminLayout>
            <Head title="Projects" />

            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Projects</h1>
                    <p className="text-gray-600 mt-1">Manage your projects</p>
                </div>
                <button
                    onClick={handleCreate}
                    className="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Project</span>
                </button>
            </div>

            <Card padding="p-0">
                <DataTable
                    columns={columns}
                    data={sampleProjects}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    emptyMessage="No projects found. Click 'Add Project' to create one."
                />
                <Pagination links={projects?.links} meta={projects?.meta} />
            </Card>

            {/* Create/Edit Modal */}
            <Modal
                show={showModal}
                onClose={() => setShowModal(false)}
                title={editingProject ? 'Edit Project' : 'Add Project'}
                maxWidth="2xl"
            >
                <form onSubmit={handleSubmit}>
                    <FormInput
                        label="Project Name"
                        name="name"
                        value={formData.name}
                        onChange={handleChange}
                        required
                        placeholder="Enter project name"
                    />

                    <FormSelect
                        label="Company"
                        name="company_id"
                        value={formData.company_id}
                        onChange={handleChange}
                        options={companyOptions}
                        required
                        placeholder="Select a company"
                    />

                    <FormTextarea
                        label="Description"
                        name="description"
                        value={formData.description}
                        onChange={handleChange}
                        placeholder="Enter project description"
                        rows={3}
                    />

                    <div className="grid grid-cols-2 gap-4">
                        <FormInput
                            label="Start Date"
                            name="start_date"
                            type="date"
                            value={formData.start_date}
                            onChange={handleChange}
                            required
                        />

                        <FormInput
                            label="End Date"
                            name="end_date"
                            type="date"
                            value={formData.end_date}
                            onChange={handleChange}
                        />
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <FormSelect
                            label="Status"
                            name="status"
                            value={formData.status}
                            onChange={handleChange}
                            options={statusOptions}
                            required
                        />

                        <FormInput
                            label="Budget"
                            name="budget"
                            value={formData.budget}
                            onChange={handleChange}
                            placeholder="$0.00"
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
                            {editingProject ? 'Update' : 'Create'}
                        </button>
                    </div>
                </form>
            </Modal>

            {/* Delete Confirmation */}
            <DeleteConfirmation
                show={showDeleteModal}
                onClose={() => setShowDeleteModal(false)}
                onConfirm={confirmDelete}
                title="Delete Project"
                message={`Are you sure you want to delete "${deletingProject?.name}"? This action cannot be undone.`}
            />
        </AdminLayout>
    );
}
