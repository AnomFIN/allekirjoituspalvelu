const statusConfig = {
  waiting: {
    label: 'Odottaa',
    classes: 'bg-amber-50 text-amber-700 border border-amber-200',
    dot: 'bg-amber-400',
  },
  signed: {
    label: 'Allekirjoitettu',
    classes: 'bg-green-50 text-green-700 border border-green-200',
    dot: 'bg-green-500',
  },
  rejected: {
    label: 'Hylätty',
    classes: 'bg-red-50 text-red-700 border border-red-200',
    dot: 'bg-red-500',
  },
  draft: {
    label: 'Luonnos',
    classes: 'bg-slate-100 text-slate-600 border border-slate-200',
    dot: 'bg-slate-400',
  },
};

export default function StatusBadge({ status }) {
  const config = statusConfig[status] || statusConfig.draft;
  return (
    <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${config.classes}`}>
      <span className={`w-1.5 h-1.5 rounded-full ${config.dot}`} />
      {config.label}
    </span>
  );
}
