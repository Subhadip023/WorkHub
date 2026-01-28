import { useState } from 'react';
import Card from './Card';
import Badge from './Badge';

export default function TaskList({ tasks = [] }) {
    const defaultTasks = [
        { id: 1, title: 'Update product descriptions', priority: 'high', completed: false },
        { id: 2, title: 'Review customer feedback', priority: 'medium', completed: false },
        { id: 3, title: 'Prepare monthly report', priority: 'high', completed: true },
        { id: 4, title: 'Update team documentation', priority: 'low', completed: false },
        { id: 5, title: 'Schedule team meeting', priority: 'medium', completed: true },
    ];

    const [taskList, setTaskList] = useState(tasks.length > 0 ? tasks : defaultTasks);

    const toggleTask = (id) => {
        setTaskList(taskList.map(task =>
            task.id === id ? { ...task, completed: !task.completed } : task
        ));
    };

    const getPriorityVariant = (priority) => {
        switch (priority) {
            case 'high':
                return 'danger';
            case 'medium':
                return 'warning';
            case 'low':
                return 'success';
            default:
                return 'gray';
        }
    };

    return (
        <Card>
            <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold text-gray-900">Tasks</h3>
                <button className="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    View All
                </button>
            </div>
            <div className="space-y-3">
                {taskList.map((task) => (
                    <div
                        key={task.id}
                        className="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        <input
                            type="checkbox"
                            checked={task.completed}
                            onChange={() => toggleTask(task.id)}
                            className="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <div className="flex-1 min-w-0">
                            <p className={`text-sm ${task.completed ? 'line-through text-gray-500' : 'text-gray-900'}`}>
                                {task.title}
                            </p>
                        </div>
                        <Badge variant={getPriorityVariant(task.priority)} size="sm">
                            {task.priority}
                        </Badge>
                    </div>
                ))}
            </div>
        </Card>
    );
}
