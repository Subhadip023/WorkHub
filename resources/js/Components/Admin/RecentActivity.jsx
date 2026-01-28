import Card from './Card';

export default function RecentActivity({ activities = [] }) {
    const defaultActivities = [
        {
            id: 1,
            user: 'John Doe',
            action: 'created a new order',
            time: '2 minutes ago',
            avatar: 'JD',
            color: 'from-blue-500 to-cyan-500',
        },
        {
            id: 2,
            user: 'Jane Smith',
            action: 'updated product inventory',
            time: '15 minutes ago',
            avatar: 'JS',
            color: 'from-purple-500 to-pink-500',
        },
        {
            id: 3,
            user: 'Mike Johnson',
            action: 'completed a task',
            time: '1 hour ago',
            avatar: 'MJ',
            color: 'from-green-500 to-teal-500',
        },
        {
            id: 4,
            user: 'Sarah Williams',
            action: 'added a new user',
            time: '2 hours ago',
            avatar: 'SW',
            color: 'from-orange-500 to-red-500',
        },
        {
            id: 5,
            user: 'Tom Brown',
            action: 'published a blog post',
            time: '3 hours ago',
            avatar: 'TB',
            color: 'from-indigo-500 to-purple-500',
        },
    ];

    const displayActivities = activities.length > 0 ? activities : defaultActivities;

    return (
        <Card>
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
            <div className="space-y-4">
                {displayActivities.map((activity, index) => (
                    <div key={activity.id || index} className="flex items-start space-x-3">
                        <div className={`w-10 h-10 rounded-full bg-gradient-to-br ${activity.color} flex items-center justify-center flex-shrink-0`}>
                            <span className="text-white text-sm font-semibold">{activity.avatar}</span>
                        </div>
                        <div className="flex-1 min-w-0">
                            <p className="text-sm text-gray-900">
                                <span className="font-medium">{activity.user}</span>{' '}
                                <span className="text-gray-600">{activity.action}</span>
                            </p>
                            <p className="text-xs text-gray-500 mt-1">{activity.time}</p>
                        </div>
                    </div>
                ))}
            </div>
        </Card>
    );
}
