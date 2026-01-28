export default function SimpleLineChart({ data = [] }) {
    const defaultData = [
        { label: 'Jan', value: 30 },
        { label: 'Feb', value: 45 },
        { label: 'Mar', value: 35 },
        { label: 'Apr', value: 55 },
        { label: 'May', value: 48 },
        { label: 'Jun', value: 65 },
        { label: 'Jul', value: 58 },
        { label: 'Aug', value: 70 },
        { label: 'Sep', value: 62 },
        { label: 'Oct', value: 75 },
        { label: 'Nov', value: 68 },
        { label: 'Dec', value: 80 },
    ];

    const chartData = data.length > 0 ? data : defaultData;
    const maxValue = Math.max(...chartData.map(d => d.value));
    const minValue = Math.min(...chartData.map(d => d.value));
    const range = maxValue - minValue;

    const getY = (value) => {
        return 100 - ((value - minValue) / range) * 100;
    };

    const points = chartData.map((item, index) => {
        const x = (index / (chartData.length - 1)) * 100;
        const y = getY(item.value);
        return `${x},${y}`;
    }).join(' ');

    const areaPoints = `0,100 ${points} 100,100`;

    return (
        <div className="w-full h-64 relative">
            <svg className="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                {/* Area gradient */}
                <defs>
                    <linearGradient id="areaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stopColor="rgb(59, 130, 246)" stopOpacity="0.3" />
                        <stop offset="100%" stopColor="rgb(59, 130, 246)" stopOpacity="0" />
                    </linearGradient>
                </defs>

                {/* Area fill */}
                <polygon
                    points={areaPoints}
                    fill="url(#areaGradient)"
                />

                {/* Line */}
                <polyline
                    points={points}
                    fill="none"
                    stroke="rgb(59, 130, 246)"
                    strokeWidth="0.5"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                />

                {/* Data points */}
                {chartData.map((item, index) => {
                    const x = (index / (chartData.length - 1)) * 100;
                    const y = getY(item.value);
                    return (
                        <g key={index}>
                            <circle
                                cx={x}
                                cy={y}
                                r="1"
                                fill="white"
                                stroke="rgb(59, 130, 246)"
                                strokeWidth="0.5"
                                className="hover:r-2 transition-all cursor-pointer"
                            />
                        </g>
                    );
                })}
            </svg>

            {/* X-axis labels */}
            <div className="flex justify-between mt-2">
                {chartData.map((item, index) => (
                    <div key={index} className="text-xs text-gray-600">
                        {item.label}
                    </div>
                ))}
            </div>
        </div>
    );
}
