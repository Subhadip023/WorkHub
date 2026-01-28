import Card from './Card';

export default function ChartCard({ title, children, action }) {
    return (
        <Card>
            <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold text-gray-900">{title}</h3>
                {action && (
                    <button className="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        {action}
                    </button>
                )}
            </div>
            <div className="mt-4">
                {children}
            </div>
        </Card>
    );
}
