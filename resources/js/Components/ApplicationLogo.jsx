import React from 'react';

export default function ApplicationLogo({ className = "w-7 h-7" }) {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 32 32"
      className={className}
      style={{ filter: "drop-shadow(0 2px 4px rgba(255, 255, 255, 0.2))" }}
    >
      <defs>
        <linearGradient id="app-logo-grad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stopColor="#ffffff" />
          <stop offset="100%" stopColor="#cbd5e1" />
        </linearGradient>
      </defs>
      <path
        d="M16 2 L28 9 L28 23 L16 30 L4 23 L4 9 Z"
        fill="none"
        stroke="url(#app-logo-grad)"
        strokeWidth="2.5"
        strokeLinejoin="round"
      />
      <path
        d="M16 8 L23 12 L23 20 L16 24 L9 20 L9 12 Z"
        fill="url(#app-logo-grad)"
        opacity="0.9"
      />
      <circle cx="16" cy="16" r="3.5" fill="#4e73df" />
    </svg>
  );
}
