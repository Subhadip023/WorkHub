export default function SimpleBarChart({ data = [] }) {
    const defaultData = [
        { label: 'Mon', value: 45 },
        { label: 'Tue', value: 52 },
        { label: 'Wed', value: 38 },
        { label: 'Thu', value: 65 },
        { label: 'Fri', value: 58 },
        { label: 'Sat', value: 42 },
        { label: 'Sun', value: 35 },
    ];

    const chartData = data.length > 0 ? data : defaultData;
    const maxValue = Math.max(...chartData.map(d => d.value));

    return (
        <div className="w-full">
            <div className="flex items-end justify-between h-64 space-x-2">
                {chartData.map((item, index) => (
                    <div key={index} className="flex-1 flex flex-col items-center">
                        <div className="w-full flex items-end justify-center h-52">
                            <div
                                className="w-full bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-lg hover:from-blue-600 hover:to-blue-500 transition-all cursor-pointer relative group"
                                style={{ height: `${(item.value / maxValue) * 100}%` }}
                            >
                                <div className="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                    {item.value}
                                </div>
                            </div>
                        </div>
                        <div className="mt-2 text-sm text-gray-600">{item.label}</div>
                    </div>
                ))}
            </div>
        </div>
    );
}
