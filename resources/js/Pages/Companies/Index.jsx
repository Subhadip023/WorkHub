import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Admin/Card';
import DataTable from '@/Components/Admin/DataTable';
import Modal from '@/Components/Admin/Modal';
import FormInput from '@/Components/Admin/FormInput';
import FormTextarea from '@/Components/Admin/FormTextarea';
import DeleteConfirmation from '@/Components/Admin/DeleteConfirmation';
import Pagination from '@/Components/Admin/Pagination';

export default function CompaniesIndex({ companies }) {
    const [showModal, setShowModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [editingCompany, setEditingCompany] = useState(null);
    const [deletingCompany, setDeletingCompany] = useState(null);
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        phone: '',
        address: '',
        website: '',
    });

    // Sample data for demonstration (with pagination)
    const sampleCompanies = companies?.data || [
        { id: 1, name: 'Tech Corp', email: 'contact@techcorp.com', phone: '+1 234 567 8900', address: '123 Tech Street, Silicon Valley, CA', website: 'www.techcorp.com' },
        { id: 2, name: 'Design Studio', email: 'hello@designstudio.com', phone: '+1 234 567 8901', address: '456 Creative Ave, New York, NY', website: 'www.designstudio.com' },
        { id: 3, name: 'Marketing Pro', email: 'info@marketingpro.com', phone: '+1 234 567 8902', address: '789 Marketing Blvd, Los Angeles, CA', website: 'www.marketingpro.com' },
        { id: 4, name: 'Software Solutions', email: 'info@softwaresolutions.com', phone: '+1 234 567 8903', address: '321 Code Lane, Austin, TX', website: 'www.softwaresolutions.com' },
        { id: 5, name: 'Creative Agency', email: 'hello@creativeagency.com', phone: '+1 234 567 8904', address: '654 Art Street, Portland, OR', website: 'www.creativeagency.com' },
        { id: 6, name: 'Digital Marketing Inc', email: 'contact@digitalmarketing.com', phone: '+1 234 567 8905', address: '987 Media Blvd, Seattle, WA', website: 'www.digitalmarketing.com' },
        { id: 7, name: 'Tech Innovations', email: 'info@techinnovations.com', phone: '+1 234 567 8906', address: '147 Innovation Dr, Boston, MA', website: 'www.techinnovations.com' },
        { id: 8, name: 'Web Development Co', email: 'contact@webdev.com', phone: '+1 234 567 8907', address: '258 Web St, Denver, CO', website: 'www.webdev.com' },
        { id: 9, name: 'Cloud Services Ltd', email: 'hello@cloudservices.com', phone: '+1 234 567 8908', address: '369 Cloud Ave, Miami, FL', website: 'www.cloudservices.com' },
        { id: 10, name: 'Data Analytics Corp', email: 'info@dataanalytics.com', phone: '+1 234 567 8909', address: '741 Data Ln, Chicago, IL', website: 'www.dataanalytics.com' },
    ];

    const columns = [
        { label: 'Name', key: 'name' },
        { label: 'Email', key: 'email' },
        { label: 'Phone', key: 'phone' },
        { label: 'Website', key: 'website' },
    ];

    const handleCreate = () => {
        setEditingCompany(null);
        setFormData({ name: '', email: '', phone: '', address: '', website: '' });
        setShowModal(true);
    };

    const handleEdit = (company) => {
        setEditingCompany(company);
        setFormData({
            name: company.name,
            email: company.email,
            phone: company.phone,
            address: company.address,
            website: company.website,
        });
        setShowModal(true);
    };

    const handleDelete = (company) => {
        setDeletingCompany(company);
        setShowDeleteModal(true);
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        if (editingCompany) {
            // router.put(route('companies.update', editingCompany.id), formData);
            console.log('Update company:', editingCompany.id, formData);
        } else {
            // router.post(route('companies.store'), formData);
            console.log('Create company:', formData);
        }

        setShowModal(false);
    };

    const confirmDelete = () => {
        // router.delete(route('companies.destroy', deletingCompany.id));
        console.log('Delete company:', deletingCompany.id);
        setShowDeleteModal(false);
    };

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    return (
        <AdminLayout>
            <Head title="Companies" />

            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Companies</h1>
                    <p className="text-gray-600 mt-1">Manage your companies</p>
                </div>
                <button
                    onClick={handleCreate}
                    className="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Company</span>
                </button>
            </div>

            <Card padding="p-0">
                <DataTable
                    columns={columns}
                    data={sampleCompanies}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    emptyMessage="No companies found. Click 'Add Company' to create one."
                />
                <Pagination links={companies?.links} meta={companies?.meta} />
            </Card>

            {/* Create/Edit Modal */}
            <Modal
                show={showModal}
                onClose={() => setShowModal(false)}
                title={editingCompany ? 'Edit Company' : 'Add Company'}
                maxWidth="2xl"
            >
                <form onSubmit={handleSubmit}>
                    <FormInput
                        label="Company Name"
                        name="name"
                        value={formData.name}
                        onChange={handleChange}
                        required
                        placeholder="Enter company name"
                    />

                    <FormInput
                        label="Email"
                        name="email"
                        type="email"
                        value={formData.email}
                        onChange={handleChange}
                        required
                        placeholder="company@example.com"
                    />

                    <FormInput
                        label="Phone"
                        name="phone"
                        type="tel"
                        value={formData.phone}
                        onChange={handleChange}
                        placeholder="+1 234 567 8900"
                    />

                    <FormInput
                        label="Website"
                        name="website"
                        value={formData.website}
                        onChange={handleChange}
                        placeholder="www.example.com"
                    />

                    <FormTextarea
                        label="Address"
                        name="address"
                        value={formData.address}
                        onChange={handleChange}
                        placeholder="Enter company address"
                        rows={3}
                    />

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
                            {editingCompany ? 'Update' : 'Create'}
                        </button>
                    </div>
                </form>
            </Modal>

            {/* Delete Confirmation */}
            <DeleteConfirmation
                show={showDeleteModal}
                onClose={() => setShowDeleteModal(false)}
                onConfirm={confirmDelete}
                title="Delete Company"
                message={`Are you sure you want to delete "${deletingCompany?.name}"? This action cannot be undone.`}
            />
        </AdminLayout>
    );
}
