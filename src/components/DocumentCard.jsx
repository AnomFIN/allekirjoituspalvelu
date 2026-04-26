import { useNavigate } from 'react-router-dom';
import StatusBadge from './StatusBadge';

export default function DocumentCard({ doc }) {
  const navigate = useNavigate();

  const signersTotal = doc.signers?.length || 0;
  const signersCompleted = doc.signers?.filter((s) => s.signed).length || 0;

  const handleActivate = () => {
    navigate(`/documents/${doc.id}`);
  };

  const handleKeyDown = (event) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      handleActivate();
    }
  };

  return (
    <div
      onClick={handleActivate}
      onKeyDown={handleKeyDown}
      role="button"
      tabIndex={0}
      className="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md hover:border-slate-300 transition-all cursor-pointer group"
    >
      <div className="flex items-start justify-between gap-3">
        {/* PDF icon */}
        <div className="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-red-100 transition-colors">
          <svg className="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>

        <div className="flex-1 min-w-0">
          <h3 className="font-semibold text-slate-900 text-sm truncate group-hover:text-blue-900 transition-colors">
            {doc.name}
          </h3>
          <p className="text-xs text-slate-400 mt-0.5">{doc.date}</p>
        </div>

        <StatusBadge status={doc.status} />
      </div>

      {/* Signers progress */}
      {signersTotal > 0 && (
        <div className="mt-4">
          <div className="flex items-center justify-between mb-1.5">
            <span className="text-xs text-slate-500">
              Allekirjoittajat {signersCompleted}/{signersTotal}
            </span>
            <span className="text-xs text-slate-400">
              {Math.round((signersCompleted / signersTotal) * 100)}%
            </span>
          </div>
          <div className="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
            <div
              className="h-full bg-green-500 rounded-full transition-all"
              style={{ width: `${(signersCompleted / signersTotal) * 100}%` }}
            />
          </div>
          <div className="mt-2.5 flex flex-wrap gap-1.5">
            {doc.signers.map((signer, i) => (
              <div
                key={i}
                className={`flex items-center gap-1.5 px-2 py-1 rounded-full text-xs border ${
                  signer.signed
                    ? 'bg-green-50 border-green-200 text-green-700'
                    : 'bg-slate-50 border-slate-200 text-slate-500'
                }`}
              >
                <div className={`w-4 h-4 rounded-full flex items-center justify-center text-white text-[10px] font-bold ${
                  signer.signed ? 'bg-green-500' : 'bg-slate-300'
                }`}>
                  {signer.name ? signer.name[0].toUpperCase() : signer.email[0].toUpperCase()}
                </div>
                <span className="max-w-[120px] truncate">{signer.name || signer.email}</span>
                {signer.signed && (
                  <svg className="w-3 h-3 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" />
                  </svg>
                )}
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
