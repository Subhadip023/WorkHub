export default function Card({ children, className = '', padding = 'p-6', shadow = true }) {
    return (
        <div className={`bg-white rounded-lg ${shadow ? 'shadow-md' : ''} ${padding} ${className}`}>
            {children}
        </div>
    );
}
