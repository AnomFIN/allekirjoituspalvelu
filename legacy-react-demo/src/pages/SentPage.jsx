import { useEffect } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';

export default function SentPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { fileName = 'Dokumentti.pdf', signers = [] } = location.state || {};

  useEffect(() => {
    // Clean up sessionStorage after successful send
    sessionStorage.removeItem('uploadedDoc');
  }, []);

  return (
    <div className="max-w-lg mx-auto pt-8">
      <div className="bg-white border border-slate-200 rounded-2xl p-8 md:p-10 text-center shadow-sm">
        {/* Success icon */}
        <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg className="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
          </svg>
        </div>

        <h1 className="text-2xl font-bold text-slate-900 mb-2">Pyyntö lähetetty!</h1>
        <p className="text-slate-500 text-sm mb-6">
          Allekirjoituspyynnöt on lähetetty onnistuneesti kaikille allekirjoittajille.
        </p>

        {/* Document */}
        <div className="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 flex items-center gap-3 mb-6 text-left">
          <svg className="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span className="text-sm font-medium text-slate-700 truncate">{fileName}</span>
        </div>

        {/* Signers */}
        {signers.length > 0 && (
          <div className="space-y-2 mb-8 text-left">
            <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
              Allekirjoituspyyntö lähetetty
            </p>
            {signers.map((s, i) => (
              <div key={i} className="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5">
                <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-xs font-bold text-blue-700 flex-shrink-0">
                  {s.name ? s.name[0].toUpperCase() : s.email[0].toUpperCase()}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-slate-900 truncate">{s.name}</p>
                  <p className="text-xs text-slate-400 truncate">{s.email}</p>
                </div>
                <div className="flex items-center gap-1 text-xs text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                  <span className="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse" />
                  Odottaa
                </div>
              </div>
            ))}
          </div>
        )}

        <div className="flex flex-col sm:flex-row gap-3">
          <button
            onClick={() => navigate('/documents')}
            className="flex-1 bg-blue-900 text-white py-3 rounded-xl font-semibold hover:bg-blue-800 transition-colors"
          >
            Näytä dokumentit
          </button>
          <button
            onClick={() => navigate('/upload')}
            className="flex-1 bg-white border border-slate-200 text-slate-700 py-3 rounded-xl font-semibold hover:bg-slate-50 transition-colors"
          >
            Lataa uusi dokumentti
          </button>
        </div>
      </div>
    </div>
  );
}
